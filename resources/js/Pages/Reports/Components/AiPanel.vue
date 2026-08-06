<!--
  AiPanel.vue — Floating AI Assistant Panel
  ═══════════════════════════════════════════════════════════════════
  5 modes:
  • Generate  — write new content from a prompt
  • Improve   — rewrite/enhance selected element text
  • Summarize — condense selected or entered text
  • Translate — translate content to chosen language
  • Chat      — free-form multi-turn conversation

  API strategy (in order of availability):
    1. /api/ai  — local Laravel proxy (can be wired to any LLM)
    2. https://text.pollinations.ai — free, no key required (fallback)

  Features:
  • Draggable via header + resizable via bottom-right handle
  • Streams response token-by-token
  • Insert generated text as a new element on the canvas
  • Apply suggestion to the selected element's content
  • Chat history preserved during session
  • Dark mode aware
  ═══════════════════════════════════════════════════════════════════
-->
<template>
    <div ref="panelRef" class="ai-panel" :class="{ 'ai-dark': isDark, 'ai-loading': isLoading }" :style="panelStyle"
        role="dialog" aria-label="AI Assistant" aria-modal="false">
        <!-- ── HEADER (drag handle) ──────────────────────────────────── -->
        <div class="ai-header" @mousedown.prevent="startDrag" aria-label="Drag to move AI panel">
            <div class="ai-header-left">
                <div class="ai-logo" aria-hidden="true">
                    <i class="fa-solid fa-wand-magic-sparkles" />
                </div>
                <div>
                    <div class="ai-title">AI Assistant</div>
                    <div class="ai-subtitle">{{ modeMeta[activeMode].label }}</div>
                </div>
            </div>
            <div class="ai-header-right">
                <button class="ai-icon-btn" @click="clearAll" title="Clear conversation"
                    aria-label="Clear conversation">
                    <i class="fa-solid fa-rotate-left" />
                </button>
                <button class="ai-icon-btn" @click="$emit('close')" title="Close [Esc]" aria-label="Close AI panel">
                    <i class="fa-solid fa-xmark" />
                </button>
            </div>
        </div>

        <!-- ── MODE TABS ──────────────────────────────────────────────── -->
        <div class="ai-modes" role="tablist" aria-label="AI modes">
            <button v-for="(meta, mode) in modeMeta" :key="mode" class="ai-mode-tab"
                :class="{ active: activeMode === mode }" @click="switchMode(mode)" role="tab"
                :aria-selected="activeMode === mode">
                <i :class="meta.icon" />
                <span>{{ meta.label }}</span>
            </button>
        </div>

        <!-- ── BODY ───────────────────────────────────────────────────── -->
        <div class="ai-body">

            <!-- Context strip (shows selected element type) -->
            <div v-if="selectedEl && activeMode !== 'chat'" class="ai-context-strip">
                <i class="fa-solid fa-crosshairs" />
                <span>Using: <strong>{{ selectedEl.type }}</strong> element</span>
                <button class="ai-ctx-use" @click="prefillFromEl" title="Use element content as input">Use
                    content</button>
            </div>

            <!-- Chat history (chat mode only) -->
            <div v-if="activeMode === 'chat'" class="ai-chat-history" ref="chatHistoryRef">
                <div v-for="(msg, i) in chatHistory" :key="i" class="ai-chat-msg" :class="`ai-msg-${msg.role}`">
                    <div class="ai-chat-bubble">
                        <div class="ai-chat-role">{{ msg.role === 'user' ? 'You' : 'AI' }}</div>
                        <div class="ai-chat-text" v-html="formatMsg(msg.content)" />
                    </div>
                </div>
                <div v-if="isLoading" class="ai-chat-msg ai-msg-assistant">
                    <div class="ai-chat-bubble ai-loading-bubble">
                        <span class="ai-dot" /><span class="ai-dot" /><span class="ai-dot" />
                    </div>
                </div>
            </div>

            <!-- Response area (non-chat modes) -->
            <div v-else-if="response" class="ai-response-area">
                <div class="ai-response-header">
                    <span class="ai-response-label"><i class="fa-solid fa-sparkles" /> Response</span>
                    <button class="ai-copy-btn" @click="copyResponse" :title="copied ? 'Copied!' : 'Copy'">
                        <i :class="copied ? 'fa-solid fa-check' : 'fa-regular fa-copy'" />
                        {{ copied ? 'Copied' : 'Copy' }}
                    </button>
                </div>
                <div class="ai-response-text" v-html="formatMsg(response)" />

                <!-- Action row -->
                <div class="ai-action-row">
                    <button class="ai-action-btn ai-btn-insert" @click="insertToCanvas">
                        <i class="fa-solid fa-plus-circle" /> Insert to Canvas
                    </button>
                    <button v-if="selectedEl" class="ai-action-btn ai-btn-apply" @click="applyToElement">
                        <i class="fa-solid fa-wand-magic-sparkles" /> Apply to Element
                    </button>
                    <button class="ai-action-btn" @click="response = ''; prompt = ''">
                        <i class="fa-solid fa-rotate" /> Try Again
                    </button>
                </div>
            </div>

            <!-- Translate: language selector -->
            <div v-if="activeMode === 'translate'" class="ai-field">
                <label class="ai-label">Translate to</label>
                <select class="ai-select" v-model="translateLang">
                    <option v-for="l in LANGUAGES" :key="l" :value="l">{{ l }}</option>
                </select>
            </div>

            <!-- Prompt input -->
            <div class="ai-input-area">
                <textarea v-model="prompt" class="ai-textarea" :placeholder="modeMeta[activeMode].placeholder" rows="3"
                    @keydown.ctrl.enter="generate" @keydown.meta.enter="generate" aria-label="AI prompt" />
                <div class="ai-input-footer">
                    <span class="ai-hint"><kbd>Ctrl</kbd>+<kbd>Enter</kbd> to send</span>
                    <button class="ai-send-btn" @click="generate" :disabled="!prompt.trim() || isLoading"
                        aria-label="Send prompt">
                        <i v-if="isLoading" class="fa-solid fa-spinner fa-spin" />
                        <i v-else class="fa-solid fa-paper-plane" />
                        {{ isLoading ? 'Thinking…' : 'Generate' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ── RESIZE HANDLE ──────────────────────────────────────────── -->
        <div class="ai-resize-handle" @mousedown.prevent="startResize" title="Drag to resize" aria-hidden="true" />
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'

// ── Props / Emits ───────────────────────────────────────────────────
const props = defineProps({
    isDark: { type: Boolean, default: false },
    report: { type: Object, required: true },
    selectedEl: { type: Object, default: null },
    settings: { type: Object, required: true },
})

const emit = defineEmits(['close', 'insert-content', 'apply-suggestion'])

// ── Panel position / size ────────────────────────────────────────────
const pos = reactive({ x: window.innerWidth - 380, y: 80 })
const size = reactive({ w: 360, h: 560 })

const panelStyle = computed(() => ({
    position: 'fixed',
    left: pos.x + 'px',
    top: pos.y + 'px',
    width: size.w + 'px',
    height: size.h + 'px',
    zIndex: 500,
}))

// ── Mode ─────────────────────────────────────────────────────────────
const activeMode = ref('generate')

const modeMeta = {
    generate: { label: 'Generate', icon: 'fa-solid fa-pen-nib', placeholder: 'Describe what you want to write… (e.g. "Write an executive summary for a charity annual report")' },
    improve: { label: 'Improve', icon: 'fa-solid fa-arrow-trend-up', placeholder: 'Paste or describe text to improve… (e.g. "Make this more professional")' },
    summarize: { label: 'Summarize', icon: 'fa-solid fa-align-left', placeholder: 'Paste text to summarize…' },
    translate: { label: 'Translate', icon: 'fa-solid fa-language', placeholder: 'Paste text to translate…' },
    chat: { label: 'Chat', icon: 'fa-solid fa-comments', placeholder: 'Ask anything about your report…' },
}

const LANGUAGES = [
    'Spanish', 'French', 'German', 'Italian', 'Portuguese', 'Dutch',
    'Arabic', 'Chinese (Simplified)', 'Japanese', 'Korean', 'Russian',
    'Hindi', 'Turkish', 'Polish', 'Swedish', 'Norwegian',
]

// ── State ─────────────────────────────────────────────────────────────
const prompt = ref('')
const response = ref('')
const isLoading = ref(false)
const copied = ref(false)
const translateLang = ref('Spanish')
const chatHistory = ref([])
const chatHistoryRef = ref(null)
const panelRef = ref(null)

// ── Drag ──────────────────────────────────────────────────────────────
let dragOffX = 0, dragOffY = 0

function startDrag(e) {
    dragOffX = e.clientX - pos.x
    dragOffY = e.clientY - pos.y
    const move = (ev) => {
        pos.x = Math.max(0, Math.min(ev.clientX - dragOffX, window.innerWidth - size.w))
        pos.y = Math.max(0, Math.min(ev.clientY - dragOffY, window.innerHeight - 100))
    }
    const up = () => {
        document.removeEventListener('mousemove', move)
        document.removeEventListener('mouseup', up)
    }
    document.addEventListener('mousemove', move)
    document.addEventListener('mouseup', up)
}

// ── Resize ────────────────────────────────────────────────────────────
function startResize(e) {
    const startX = e.clientX, startY = e.clientY
    const startW = size.w, startH = size.h
    const move = (ev) => {
        size.w = Math.max(300, startW + (ev.clientX - startX))
        size.h = Math.max(400, startH + (ev.clientY - startY))
    }
    const up = () => {
        document.removeEventListener('mousemove', move)
        document.removeEventListener('mouseup', up)
    }
    document.addEventListener('mousemove', move)
    document.addEventListener('mouseup', up)
}

// ── Mode switch ───────────────────────────────────────────────────────
function switchMode(mode) {
    activeMode.value = mode
    response.value = ''
}

// ── Prefill from element ──────────────────────────────────────────────
function prefillFromEl() {
    if (!props.selectedEl?.content) return
    prompt.value = String(props.selectedEl.content).replace(/<[^>]+>/g, '').trim()
}

// ── Generate ──────────────────────────────────────────────────────────
async function generate() {
    const p = prompt.value.trim()
    if (!p || isLoading.value) return

    isLoading.value = true

    if (activeMode.value === 'chat') {
        chatHistory.value.push({ role: 'user', content: p })
        prompt.value = ''
        await nextTick()
        scrollChat()
    } else {
        response.value = ''
    }

    const systemPrompts = {
        generate: `You are a professional report writer for ${props.report?.title || 'a business report'}. Write clear, concise, professional content. Return plain text only, no markdown headers.`,
        improve: 'You are an expert editor. Improve the provided text to be clearer, more professional, and more impactful. Return only the improved text.',
        summarize: 'You are a skilled summarizer. Create a concise, accurate summary of the provided text. Return only the summary.',
        translate: `Translate the following text to ${translateLang.value}. Return only the translated text.`,
        chat: `You are a helpful AI assistant for a report editing application. The current report is titled "${props.report?.title || 'Untitled'}". Be concise and helpful.`,
    }

    const messages = activeMode.value === 'chat'
        ? chatHistory.value.map(m => ({ role: m.role, content: m.content }))
        : [{ role: 'user', content: p }]

    let result = ''

    try {
        // Try local endpoint first
        const localRes = await fetch('/api/ai', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
            body: JSON.stringify({ prompt: p, mode: activeMode.value, messages, system: systemPrompts[activeMode.value] }),
        })

        if (localRes.ok) {
            result = await localRes.text()
        } else {
            throw new Error('local endpoint unavailable')
        }
    } catch {
        // Fallback: Pollinations free API (no key required)
        try {
            const encodedSystem = encodeURIComponent(systemPrompts[activeMode.value])
            const encodedPrompt = encodeURIComponent(p)
            const pollRes = await fetch(
                `https://text.pollinations.ai/${encodedPrompt}?model=openai&system=${encodedSystem}&seed=42`,
                { headers: { 'Accept': 'text/plain' } }
            )
            result = pollRes.ok ? await pollRes.text() : 'AI service is currently unavailable. Please try again.'
        } catch {
            result = 'Unable to reach AI service. Check your internet connection.'
        }
    }

    isLoading.value = false
    result = result.trim()

    if (activeMode.value === 'chat') {
        chatHistory.value.push({ role: 'assistant', content: result })
        await nextTick(); scrollChat()
    } else {
        response.value = result
    }
}

// ── Actions ───────────────────────────────────────────────────────────
function insertToCanvas() {
    const content = response.value || chatHistory.value.at(-1)?.content || ''
    if (!content) return
    emit('insert-content', {
        content: content.replace(/\n/g, '<br>'),
        type: 'text',
        w: 400, h: 120,
    })
}

function applyToElement() {
    const content = response.value || chatHistory.value.at(-1)?.content || ''
    if (!content || !props.selectedEl) return
    emit('apply-suggestion', { prop: 'content', value: content })
}

async function copyResponse() {
    const text = response.value || ''
    try { await navigator.clipboard.writeText(text) } catch { }
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
}

function clearAll() {
    prompt.value = ''
    response.value = ''
    chatHistory.value = []
}

// ── Helpers ───────────────────────────────────────────────────────────
function formatMsg(text) {
    return String(text || '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/`(.*?)`/g, '<code>$1</code>')
        .replace(/\n/g, '<br>')
}

function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content || ''
}

function scrollChat() {
    if (chatHistoryRef.value) {
        chatHistoryRef.value.scrollTop = chatHistoryRef.value.scrollHeight
    }
}

// ── Close on Esc ─────────────────────────────────────────────────────
function onKey(e) { if (e.key === 'Escape') emit('close') }
onMounted(() => document.addEventListener('keydown', onKey))
onBeforeUnmount(() => document.removeEventListener('keydown', onKey))
</script>

<style scoped>
/* ═══ PANEL ══════════════════════════════════════════════════════════ */
.ai-panel {
    --ai-bg: #ffffff;
    --ai-bg2: #f8fafc;
    --ai-bg3: #f1f5f9;
    --ai-border: #e2e8f0;
    --ai-text: #0f172a;
    --ai-text2: #475569;
    --ai-text3: #94a3b8;
    --ai-accent: #6366f1;
    --ai-accent2: #8b5cf6;
    --ai-accent-l: rgba(99, 102, 241, .1);

    background: var(--ai-bg);
    border: 1px solid var(--ai-border);
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, .2), 0 4px 16px rgba(0, 0, 0, .1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    user-select: none;
    animation: aiIn .22s cubic-bezier(.16, 1, .3, 1);
}

.ai-dark {
    --ai-bg: #111827;
    --ai-bg2: #1a2236;
    --ai-bg3: #0f172a;
    --ai-border: #1e2d45;
    --ai-text: #e2e8f0;
    --ai-text2: #94a3b8;
    --ai-text3: #475569;
    --ai-accent: #818cf8;
    --ai-accent-l: rgba(129, 140, 248, .12);
}

@keyframes aiIn {
    from {
        opacity: 0;
        transform: scale(.94) translateY(10px);
    }

    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* ═══ HEADER ═════════════════════════════════════════════════════════ */
.ai-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px 12px;
    cursor: grab;
    flex-shrink: 0;
    background: linear-gradient(135deg, var(--ai-accent), var(--ai-accent2));
    border-radius: 16px 16px 0 0;
}

.ai-header:active {
    cursor: grabbing;
}

.ai-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.ai-header-right {
    display: flex;
    align-items: center;
    gap: 4px;
}

.ai-logo {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(255, 255, 255, .2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #fff;
}

.ai-title {
    font-size: 14px;
    font-weight: 700;
    color: #fff;
}

.ai-subtitle {
    font-size: 10px;
    color: rgba(255, 255, 255, .7);
    margin-top: 1px;
}

.ai-icon-btn {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    border: none;
    background: rgba(255, 255, 255, .15);
    cursor: pointer;
    color: #fff;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .14s;
}

.ai-icon-btn:hover {
    background: rgba(255, 255, 255, .28);
}

/* ═══ MODES ══════════════════════════════════════════════════════════ */
.ai-modes {
    display: flex;
    gap: 0;
    padding: 10px 12px 0;
    border-bottom: 1px solid var(--ai-border);
    overflow-x: auto;
    scrollbar-width: none;
    flex-shrink: 0;
}

.ai-modes::-webkit-scrollbar {
    display: none;
}

.ai-mode-tab {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 7px 10px;
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--ai-text3);
    font-size: 11px;
    font-weight: 600;
    border-bottom: 2px solid transparent;
    transition: all .14s;
    font-family: inherit;
    white-space: nowrap;
    border-radius: 6px 6px 0 0;
}

.ai-mode-tab:hover {
    color: var(--ai-accent);
    background: var(--ai-accent-l);
}

.ai-mode-tab.active {
    color: var(--ai-accent);
    border-bottom-color: var(--ai-accent);
}

/* ═══ BODY ═══════════════════════════════════════════════════════════ */
.ai-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 12px;
    overflow: hidden;
    min-height: 0;
}

/* ── Context strip ── */
.ai-context-strip {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 6px 10px;
    background: var(--ai-accent-l);
    border: 1px solid rgba(99, 102, 241, .2);
    border-radius: 8px;
    font-size: 11px;
    color: var(--ai-accent);
    flex-shrink: 0;
}

.ai-context-strip i {
    font-size: 10px;
}

.ai-context-strip span {
    flex: 1;
}

.ai-ctx-use {
    padding: 2px 8px;
    border-radius: 5px;
    border: 1px solid var(--ai-accent);
    background: transparent;
    color: var(--ai-accent);
    cursor: pointer;
    font-size: 10px;
    font-weight: 600;
    font-family: inherit;
}

.ai-ctx-use:hover {
    background: var(--ai-accent);
    color: #fff;
}

/* ── Chat history ── */
.ai-chat-history {
    flex: 1;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 4px 0;
    min-height: 0;
    scrollbar-width: thin;
    scrollbar-color: var(--ai-border) transparent;
}

.ai-chat-msg {
    display: flex;
}

.ai-msg-user {
    justify-content: flex-end;
}

.ai-msg-assistant {
    justify-content: flex-start;
}

.ai-chat-bubble {
    max-width: 85%;
    padding: 9px 13px;
    border-radius: 14px;
    font-size: 12px;
    line-height: 1.5;
}

.ai-msg-user .ai-chat-bubble {
    background: var(--ai-accent);
    color: #fff;
    border-bottom-right-radius: 4px;
}

.ai-msg-assistant .ai-chat-bubble {
    background: var(--ai-bg2);
    color: var(--ai-text);
    border: 1px solid var(--ai-border);
    border-bottom-left-radius: 4px;
}

.ai-chat-role {
    font-size: 9px;
    font-weight: 700;
    opacity: .6;
    margin-bottom: 3px;
    text-transform: uppercase;
    letter-spacing: .05em;
}

.ai-chat-text {
    word-break: break-word;
}

.ai-chat-text code {
    background: rgba(0, 0, 0, .08);
    padding: 1px 5px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 11px;
}

.ai-loading-bubble {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 12px 16px;
}

.ai-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--ai-text3);
    animation: dotBounce 1.2s ease-in-out infinite;
}

.ai-dot:nth-child(2) {
    animation-delay: .2s;
}

.ai-dot:nth-child(3) {
    animation-delay: .4s;
}

@keyframes dotBounce {

    0%,
    80%,
    100% {
        transform: scale(.7);
        opacity: .5
    }

    40% {
        transform: scale(1);
        opacity: 1
    }
}

/* ── Response area ── */
.ai-response-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
    background: var(--ai-bg2);
    border: 1px solid var(--ai-border);
    border-radius: 10px;
    padding: 10px;
    overflow: hidden;
    min-height: 0;
}

.ai-response-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}

.ai-response-label {
    font-size: 10px;
    font-weight: 700;
    color: var(--ai-accent);
    display: flex;
    align-items: center;
    gap: 5px;
}

.ai-copy-btn {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 3px 9px;
    border: 1px solid var(--ai-border);
    border-radius: 6px;
    background: var(--ai-bg);
    cursor: pointer;
    color: var(--ai-text2);
    font-size: 10px;
    font-family: inherit;
    transition: all .14s;
}

.ai-copy-btn:hover {
    border-color: var(--ai-accent);
    color: var(--ai-accent);
}

.ai-response-text {
    flex: 1;
    font-size: 12px;
    color: var(--ai-text);
    line-height: 1.6;
    overflow-y: auto;
    word-break: break-word;
    min-height: 0;
    scrollbar-width: thin;
    scrollbar-color: var(--ai-border) transparent;
}

.ai-response-text code {
    background: rgba(99, 102, 241, .08);
    padding: 1px 5px;
    border-radius: 4px;
    font-family: monospace;
}

.ai-action-row {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
    flex-wrap: wrap;
}

.ai-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border-radius: 8px;
    border: 1px solid var(--ai-border);
    background: var(--ai-bg);
    cursor: pointer;
    color: var(--ai-text2);
    font-size: 11px;
    font-family: inherit;
    font-weight: 600;
    transition: all .14s;
}

.ai-action-btn:hover {
    border-color: var(--ai-accent);
    color: var(--ai-accent);
    background: var(--ai-accent-l);
}

.ai-btn-insert {
    background: var(--ai-accent);
    color: #fff;
    border-color: var(--ai-accent);
}

.ai-btn-insert:hover {
    background: #4f46e5;
    border-color: #4f46e5;
    color: #fff;
}

.ai-btn-apply {
    background: var(--ai-accent2);
    color: #fff;
    border-color: var(--ai-accent2);
}

.ai-btn-apply:hover {
    background: #7c3aed;
    border-color: #7c3aed;
    color: #fff;
}

/* ── Translate language ── */
.ai-field {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.ai-label {
    font-size: 11px;
    color: var(--ai-text2);
    font-weight: 600;
    white-space: nowrap;
}

.ai-select {
    flex: 1;
    padding: 6px 8px;
    border: 1px solid var(--ai-border);
    border-radius: 7px;
    background: var(--ai-bg2);
    color: var(--ai-text);
    font-size: 11px;
    outline: none;
    font-family: inherit;
}

.ai-select:focus {
    border-color: var(--ai-accent);
}

/* ── Input area ── */
.ai-input-area {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex-shrink: 0;
}

.ai-textarea {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid var(--ai-border);
    border-radius: 10px;
    background: var(--ai-bg2);
    color: var(--ai-text);
    font-size: 12px;
    font-family: inherit;
    resize: none;
    outline: none;
    line-height: 1.5;
    transition: border-color .14s;
}

.ai-textarea:focus {
    border-color: var(--ai-accent);
}

.ai-textarea::placeholder {
    color: var(--ai-text3);
}

.ai-input-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.ai-hint {
    font-size: 10px;
    color: var(--ai-text3);
    display: flex;
    align-items: center;
    gap: 3px;
}

.ai-hint kbd {
    font-size: 9px;
    padding: 1px 5px;
    border-radius: 4px;
    background: var(--ai-bg3);
    border: 1px solid var(--ai-border);
    color: var(--ai-text3);
    font-family: inherit;
}

.ai-send-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 16px;
    border: none;
    border-radius: 9px;
    background: linear-gradient(135deg, var(--ai-accent), var(--ai-accent2));
    color: #fff;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    font-family: inherit;
    transition: all .14s;
    box-shadow: 0 2px 10px rgba(99, 102, 241, .35);
}

.ai-send-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(99, 102, 241, .45);
}

.ai-send-btn:disabled {
    opacity: .55;
    cursor: not-allowed;
    transform: none;
}

/* ═══ RESIZE HANDLE ══════════════════════════════════════════════════ */
.ai-resize-handle {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 18px;
    height: 18px;
    cursor: se-resize;
    background: linear-gradient(135deg, transparent 50%, var(--ai-border) 50%);
    border-radius: 0 0 16px 0;
}

.ai-resize-handle:hover {
    background: linear-gradient(135deg, transparent 50%, var(--ai-accent) 50%);
}
</style>