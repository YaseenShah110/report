<!--
  RichTextEditor.vue — Full WYSIWYG inline editor
  • Toolbar: Bold, Italic, Underline, Strike, Code, H1/H2/H3, Lists, Quote
  • Alignment: Left, Center, Right, Justify
  • Text color, highlight color
  • Link insertion / removal
  • Undo / Redo
  • Table insertion (basic)
  • Font size selector
  • All via execCommand for zero-dependency simplicity
  • Emits update:content on every change
  • Respects :editable prop
  • Themed with primary-color prop
-->
<template>
    <div class="rte-root" :class="{ 'rte-editable': editable, 'rte-readonly': !editable }">

        <!-- Toolbar (shown only when editable) -->
        <div v-if="editable" class="rte-toolbar" @mousedown.prevent>

            <!-- History -->
            <div class="rte-group">
                <ToolBtn icon="fa-solid fa-rotate-left" title="Undo" @click="cmd('undo')" />
                <ToolBtn icon="fa-solid fa-rotate-right" title="Redo" @click="cmd('redo')" />
            </div>

            <div class="rte-divider" />

            <!-- Text style -->
            <div class="rte-group">
                <ToolBtn icon="fa-solid fa-bold" title="Bold" :active="states.bold" @click="cmd('bold')" />
                <ToolBtn icon="fa-solid fa-italic" title="Italic" :active="states.italic" @click="cmd('italic')" />
                <ToolBtn icon="fa-solid fa-underline" title="Underline" :active="states.underline"
                    @click="cmd('underline')" />
                <ToolBtn icon="fa-solid fa-strikethrough" title="Strikethrough" :active="states.strikethrough"
                    @click="cmd('strikeThrough')" />
                <ToolBtn icon="fa-solid fa-code" title="Code" :active="false" @click="wrapCode" />
            </div>

            <div class="rte-divider" />

            <!-- Headings -->
            <div class="rte-group">
                <select class="rte-select" @change="onFormatBlock" :value="currentBlock">
                    <option value="p">Paragraph</option>
                    <option value="h1">Heading 1</option>
                    <option value="h2">Heading 2</option>
                    <option value="h3">Heading 3</option>
                    <option value="h4">Heading 4</option>
                    <option value="blockquote">Quote</option>
                    <option value="pre">Code Block</option>
                </select>
            </div>

            <div class="rte-divider" />

            <!-- Font size -->
            <div class="rte-group">
                <select class="rte-select rte-size" @change="onFontSize" :value="currentSize">
                    <option v-for="s in SIZES" :key="s.v" :value="s.v">{{ s.l }}</option>
                </select>
            </div>

            <div class="rte-divider" />

            <!-- Alignment -->
            <div class="rte-group">
                <ToolBtn icon="fa-solid fa-align-left" title="Align Left" :active="states.justifyLeft"
                    @click="cmd('justifyLeft')" />
                <ToolBtn icon="fa-solid fa-align-center" title="Center" :active="states.justifyCenter"
                    @click="cmd('justifyCenter')" />
                <ToolBtn icon="fa-solid fa-align-right" title="Align Right" :active="states.justifyRight"
                    @click="cmd('justifyRight')" />
                <ToolBtn icon="fa-solid fa-align-justify" title="Justify" :active="states.justifyFull"
                    @click="cmd('justifyFull')" />
            </div>

            <div class="rte-divider" />

            <!-- Lists -->
            <div class="rte-group">
                <ToolBtn icon="fa-solid fa-list-ul" title="Bullet List" :active="states.insertUnorderedList"
                    @click="cmd('insertUnorderedList')" />
                <ToolBtn icon="fa-solid fa-list-ol" title="Numbered List" :active="states.insertOrderedList"
                    @click="cmd('insertOrderedList')" />
                <ToolBtn icon="fa-solid fa-indent" title="Indent" @click="cmd('indent')" />
                <ToolBtn icon="fa-solid fa-outdent" title="Outdent" @click="cmd('outdent')" />
            </div>

            <div class="rte-divider" />

            <!-- Colors -->
            <div class="rte-group">
                <div class="rte-color-btn" title="Text Color">
                    <i class="fa-solid fa-font" :style="{ color: textColor }" />
                    <input type="color" :value="textColor" @input="onTextColor" class="rte-color-input" />
                    <div class="rte-color-bar" :style="{ background: textColor }" />
                </div>
                <div class="rte-color-btn" title="Highlight">
                    <i class="fa-solid fa-highlighter" :style="{ color: hlColor }" />
                    <input type="color" :value="hlColor" @input="onHlColor" class="rte-color-input" />
                    <div class="rte-color-bar" :style="{ background: hlColor }" />
                </div>
            </div>

            <div class="rte-divider" />

            <!-- Link -->
            <div class="rte-group">
                <ToolBtn icon="fa-solid fa-link" title="Insert Link" @click="insertLink" />
                <ToolBtn icon="fa-solid fa-link-slash" title="Remove Link" @click="cmd('unlink')" />
            </div>

            <div class="rte-divider" />

            <!-- Clear -->
            <div class="rte-group">
                <ToolBtn icon="fa-solid fa-eraser" title="Clear Formatting" @click="cmd('removeFormat')" />
            </div>

        </div><!-- rte-toolbar -->

        <!-- Editable content area -->
        <div ref="editorRef" class="rte-content" :contenteditable="editable ? 'true' : 'false'" :spellcheck="editable"
            @input="onInput" @keyup="updateStates" @mouseup="updateStates" @keydown="onKeyDown" @paste="onPaste"
            v-html="initialContent" />

        <!-- Link popup (mini) -->
        <div v-if="linkPopup.show" class="rte-link-popup"
            :style="{ top: linkPopup.y + 'px', left: linkPopup.x + 'px' }">
            <input v-model="linkPopup.url" class="rte-link-input" placeholder="https://" @keydown.enter="confirmLink"
                @keydown.escape="linkPopup.show = false" />
            <button class="rte-link-ok" @click="confirmLink">OK</button>
            <button class="rte-link-cancel" @click="linkPopup.show = false">✕</button>
        </div>

    </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch, nextTick, defineComponent, h } from 'vue'

const props = defineProps({
    content: { type: String, default: '' },
    editable: { type: Boolean, default: true },
    primaryColor: { type: String, default: '#6366f1' },
})

const emit = defineEmits(['update:content'])

// ── Inline ToolBtn component ─────────────────────────────────────────────────
const ToolBtn = defineComponent({
    props: { icon: String, title: String, active: Boolean },
    emits: ['click'],
    setup(p, { emit: e }) {
        return () => h('button', {
            class: ['rte-btn', p.active ? 'rte-btn--active' : ''],
            title: p.title,
            onClick: () => e('click'),
        }, [h('i', { class: p.icon })])
    }
})

// ── Refs & state ──────────────────────────────────────────────────────────────
const editorRef = ref(null)
const initialContent = ref(props.content || '<p>Start typing…</p>')

const states = reactive({
    bold: false, italic: false, underline: false, strikethrough: false,
    justifyLeft: false, justifyCenter: false, justifyRight: false, justifyFull: false,
    insertUnorderedList: false, insertOrderedList: false,
})

const textColor = ref('#000000')
const hlColor = ref('#ffff00')
const currentBlock = ref('p')
const currentSize = ref('3')

const linkPopup = reactive({ show: false, url: '', x: 0, y: 0, savedRange: null })

let savedSelection = null

// ── Font sizes (execCommand fontSize uses 1-7) ────────────────────────────────
const SIZES = [
    { v: '1', l: '8px' }, { v: '2', l: '10px' }, { v: '3', l: '12px' },
    { v: '4', l: '14px' }, { v: '5', l: '18px' }, { v: '6', l: '24px' },
    { v: '7', l: '36px' },
]

// ── Core execCommand wrapper ──────────────────────────────────────────────────
function cmd(command, value = null) {
    editorRef.value?.focus()
    document.execCommand(command, false, value)
    updateStates()
    emit('update:content', editorRef.value?.innerHTML || '')
}

// ── State polling ─────────────────────────────────────────────────────────────
function updateStates() {
    const cmds = ['bold', 'italic', 'underline', 'strikeThrough', 'justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull', 'insertUnorderedList', 'insertOrderedList']
    cmds.forEach(c => {
        const key = c === 'strikeThrough' ? 'strikethrough' : c
        try { states[key] = document.queryCommandState(c) } catch { }
    })
    // Block type
    try { currentBlock.value = document.queryCommandValue('formatBlock').toLowerCase() || 'p' } catch { }
    // Text color
    try {
        const c = document.queryCommandValue('foreColor')
        if (c) textColor.value = rgbToHex(c)
    } catch { }
}

// ── Format block ──────────────────────────────────────────────────────────────
function onFormatBlock(e) { cmd('formatBlock', e.target.value) }

// ── Font size ─────────────────────────────────────────────────────────────────
function onFontSize(e) { currentSize.value = e.target.value; cmd('fontSize', e.target.value) }

// ── Colors ────────────────────────────────────────────────────────────────────
function onTextColor(e) { textColor.value = e.target.value; cmd('foreColor', e.target.value) }
function onHlColor(e) { hlColor.value = e.target.value; cmd('hiliteColor', e.target.value) }

// ── Code inline ───────────────────────────────────────────────────────────────
function wrapCode() {
    const sel = window.getSelection()
    if (!sel || sel.rangeCount === 0) return
    const range = sel.getRangeAt(0)
    const code = document.createElement('code')
    code.style.cssText = `font-family:'Fira Code',monospace;background:rgba(0,0,0,.08);padding:1px 5px;border-radius:3px;font-size:.9em`
    range.surroundContents(code)
    emit('update:content', editorRef.value?.innerHTML || '')
}

// ── Link ─────────────────────────────────────────────────────────────────────
function insertLink() {
    const sel = window.getSelection()
    if (sel && sel.rangeCount > 0) {
        savedSelection = sel.getRangeAt(0).cloneRange()
        const rect = sel.getRangeAt(0).getBoundingClientRect()
        const rootRect = editorRef.value?.closest('.rte-root')?.getBoundingClientRect() || { left: 0, top: 0 }
        linkPopup.x = rect.left - rootRect.left
        linkPopup.y = rect.bottom - rootRect.top + 4
        linkPopup.url = ''
        linkPopup.show = true
    }
    nextTick(() => document.querySelector('.rte-link-input')?.focus())
}

function confirmLink() {
    const url = linkPopup.url.trim()
    if (!url) { linkPopup.show = false; return }
    editorRef.value?.focus()
    if (savedSelection) {
        const sel = window.getSelection()
        sel.removeAllRanges()
        sel.addRange(savedSelection)
    }
    document.execCommand('createLink', false, url.startsWith('http') ? url : 'https://' + url)
    editorRef.value?.querySelectorAll('a').forEach(a => {
        a.setAttribute('target', '_blank')
        a.style.color = props.primaryColor
    })
    linkPopup.show = false
    emit('update:content', editorRef.value?.innerHTML || '')
}

// ── Input ─────────────────────────────────────────────────────────────────────
function onInput() {
    emit('update:content', editorRef.value?.innerHTML || '')
    updateStates()
}

// ── Keyboard shortcuts ────────────────────────────────────────────────────────
function onKeyDown(e) {
    const mod = e.ctrlKey || e.metaKey
    if (!mod) return
    const keyMap = { b: 'bold', i: 'italic', u: 'underline', z: 'undo', y: 'redo' }
    if (keyMap[e.key]) { e.preventDefault(); cmd(keyMap[e.key]) }
    if (e.key === 'z' && e.shiftKey) { e.preventDefault(); cmd('redo') }
}

// ── Paste: strip html by default ─────────────────────────────────────────────
function onPaste(e) {
    const html = e.clipboardData.getData('text/html')
    const text = e.clipboardData.getData('text/plain')
    if (!html && !text) return
    e.preventDefault()
    // Allow HTML paste but clean dangerous tags
    const clean = html ? sanitize(html) : text.replace(/\n/g, '<br>')
    document.execCommand('insertHTML', false, clean)
    emit('update:content', editorRef.value?.innerHTML || '')
}

function sanitize(html) {
    const tmp = document.createElement('div')
    tmp.innerHTML = html
    tmp.querySelectorAll('script,style,iframe,object,embed').forEach(n => n.remove())
    tmp.querySelectorAll('[onclick],[onmouseover],[onerror],[style]').forEach(n => {
        n.removeAttribute('onclick'); n.removeAttribute('onmouseover')
        n.removeAttribute('onerror'); n.removeAttribute('style')
    })
    return tmp.innerHTML
}

// ── Utilities ─────────────────────────────────────────────────────────────────
function rgbToHex(rgb) {
    const m = rgb.match(/\d+/g)
    if (!m || m.length < 3) return '#000000'
    return '#' + m.slice(0, 3).map(v => (+v).toString(16).padStart(2, '0')).join('')
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
    if (editorRef.value) {
        editorRef.value.innerHTML = props.content || '<p>Start typing…</p>'
        if (props.editable) updateStates()
    }
})

watch(() => props.content, val => {
    if (editorRef.value && !editorRef.value.matches(':focus')) {
        editorRef.value.innerHTML = val || '<p>Start typing…</p>'
    }
})

watch(() => props.editable, val => {
    if (val && editorRef.value) nextTick(() => updateStates())
})
</script>

<style scoped>
/* ── Root ─────────────────────────────────────────────────────────────────────── */
.rte-root {
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;
    position: relative;
    border-radius: 6px;
    overflow: hidden;
}

.rte-editable {
    border: 1px solid rgba(99, 102, 241, .35);
}

.rte-editable:focus-within {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, .12);
}

/* ── Toolbar ─────────────────────────────────────────────────────────────────── */
.rte-toolbar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 1px;
    padding: 4px 6px;
    border-bottom: 1px solid rgba(99, 102, 241, .15);
    background: var(--bg-secondary, #f8fafc);
    overflow-x: auto;
    scrollbar-width: none;
    flex-shrink: 0;
}

.rte-toolbar::-webkit-scrollbar {
    display: none;
}

.rte-group {
    display: flex;
    align-items: center;
    gap: 1px;
}

.rte-divider {
    width: 1px;
    height: 18px;
    background: var(--border, #e2e8f0);
    margin: 0 3px;
    flex-shrink: 0;
}

/* ── Buttons ─────────────────────────────────────────────────────────────────── */
.rte-btn {
    width: 24px;
    height: 24px;
    border: none;
    background: transparent;
    border-radius: 5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-secondary, #475569);
    font-size: 11px;
    transition: all .12s;
    flex-shrink: 0;
}

.rte-btn:hover {
    background: rgba(99, 102, 241, .1);
    color: #6366f1;
}

.rte-btn--active {
    background: rgba(99, 102, 241, .15) !important;
    color: #6366f1 !important;
}

/* ── Selects ─────────────────────────────────────────────────────────────────── */
.rte-select {
    padding: 2px 4px;
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 5px;
    background: var(--bg-panel, #fff);
    color: var(--text-primary, #0f172a);
    font-size: 10px;
    cursor: pointer;
    outline: none;
    font-family: inherit;
    height: 24px;
}

.rte-select:focus {
    border-color: #6366f1;
}

.rte-size {
    width: 54px;
}

/* ── Color buttons ───────────────────────────────────────────────────────────── */
.rte-color-btn {
    position: relative;
    width: 28px;
    height: 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border-radius: 5px;
    padding: 2px;
    transition: background .12s;
}

.rte-color-btn:hover {
    background: rgba(99, 102, 241, .1);
}

.rte-color-btn i {
    font-size: 11px;
    pointer-events: none;
}

.rte-color-bar {
    width: 14px;
    height: 3px;
    border-radius: 2px;
    margin-top: 1px;
    flex-shrink: 0;
}

.rte-color-input {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
    padding: 0;
    border: none;
}

/* ── Content ─────────────────────────────────────────────────────────────────── */
.rte-content {
    flex: 1;
    padding: 10px 12px;
    outline: none;
    overflow-y: auto;
    font-size: 13px;
    line-height: 1.65;
    color: var(--text-primary, #1e293b);
    word-break: break-word;
    scrollbar-width: thin;
    min-height: 60px;
}

.rte-content:empty::before {
    content: 'Start typing…';
    color: var(--text-muted, #94a3b8);
    pointer-events: none;
}

.rte-content h1 {
    font-size: 2em;
    font-weight: 800;
    margin: .4em 0;
    line-height: 1.2;
}

.rte-content h2 {
    font-size: 1.5em;
    font-weight: 700;
    margin: .4em 0;
    line-height: 1.3;
}

.rte-content h3 {
    font-size: 1.2em;
    font-weight: 700;
    margin: .3em 0;
}

.rte-content h4 {
    font-size: 1em;
    font-weight: 600;
    margin: .3em 0;
}

.rte-content p {
    margin: .3em 0;
}

.rte-content ul {
    padding-left: 1.5em;
    margin: .3em 0;
}

.rte-content ol {
    padding-left: 1.5em;
    margin: .3em 0;
}

.rte-content li {
    margin: .15em 0;
}

.rte-content blockquote {
    border-left: 4px solid #6366f1;
    padding: 6px 12px;
    margin: .5em 0;
    font-style: italic;
    color: #64748b;
    background: rgba(99, 102, 241, .04);
    border-radius: 0 5px 5px 0;
}

.rte-content pre {
    background: #1e293b;
    color: #34d399;
    border-radius: 6px;
    padding: 10px 14px;
    font-family: 'Fira Code', monospace;
    font-size: .85em;
    overflow-x: auto;
    margin: .4em 0;
}

.rte-content code {
    font-family: 'Fira Code', monospace;
    background: rgba(0, 0, 0, .08);
    padding: 1px 5px;
    border-radius: 3px;
    font-size: .88em;
}

.rte-content a {
    color: #6366f1;
    text-decoration: underline;
}

.rte-content a:hover {
    color: #4f46e5;
}

.rte-content strong {
    font-weight: 700;
}

.rte-content em {
    font-style: italic;
}

.rte-content table {
    border-collapse: collapse;
    width: 100%;
    margin: .5em 0;
    font-size: .9em;
}

.rte-content td,
.rte-content th {
    border: 1px solid #e2e8f0;
    padding: 5px 9px;
}

.rte-content th {
    background: #f8fafc;
    font-weight: 700;
}

/* ── Link popup ──────────────────────────────────────────────────────────────── */
.rte-link-popup {
    position: absolute;
    z-index: 200;
    display: flex;
    align-items: center;
    gap: 4px;
    background: var(--bg-panel, #fff);
    border: 1px solid #6366f1;
    border-radius: 7px;
    padding: 5px 7px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, .12);
}

.rte-link-input {
    flex: 1;
    min-width: 180px;
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 5px;
    padding: 4px 7px;
    font-size: 11px;
    outline: none;
    background: var(--bg-secondary, #f8fafc);
    font-family: inherit;
}

.rte-link-input:focus {
    border-color: #6366f1;
}

.rte-link-ok,
.rte-link-cancel {
    padding: 3px 9px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 11px;
    font-family: inherit;
    font-weight: 600;
}

.rte-link-ok {
    background: #6366f1;
    color: #fff;
}

.rte-link-ok:hover {
    background: #4f46e5;
}

.rte-link-cancel {
    background: var(--bg-secondary, #f8fafc);
    color: var(--text-muted, #94a3b8);
}
</style>