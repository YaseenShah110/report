<!--
  AiPanel.vue — Floating AI Assistant Panel
  • Draggable, resizable, minimizable, maximizable
  • 6 modes: Generate | Enhance | Summarize | Chart | Translate | Image
  • Calls /api/ai/generate (local) or Pollinations free API
  • Markdown rendering (bold, italic, code, lists, headings)
  • Voice input (Web Speech API)
  • Chat history (localStorage, 20 sessions)
  • Feedback thumbs + regenerate
  • Insert content / chart directly into canvas
  • Copy to clipboard
  • Token estimate display
  • Quick prompts per mode
  • Dark mode aware
  • Mobile: full-width bottom sheet
  • Memory safe: all listeners cleaned on unmount
-->
<template>
    <Teleport to="body">
        <Transition name="ai-emerge">
            <div v-if="visible" ref="panelRef" class="ai-panel"
                :class="[isDark && 'ai-dark', isMaximized && 'ai-maximized', isMinimized && 'ai-minimized']"
                :style="panelStyle" role="dialog" aria-modal="false" aria-label="AI Assistant">
                <!-- ── Header / drag handle ──────────────────────────────── -->
                <div class="ai-header" @mousedown="startDrag" @touchstart.prevent="startDragTouch">
                    <div class="ai-orb" aria-hidden="true">
                        <span class="ai-orb-ring" />
                        <i class="fa-solid fa-wand-magic-sparkles ai-orb-icon" />
                    </div>

                    <div class="ai-title-area">
                        <span class="ai-title">AI Assistant</span>
                        <span class="ai-status" :class="isLoading && 'ai-status--busy'" aria-live="polite">
                            <span class="ai-status-dot" aria-hidden="true" />
                            {{ isLoading ? thinkingLabel : 'Ready' }}
                        </span>
                    </div>

                    <div class="ai-header-controls">
                        <span class="ai-token-count" :title="`~${tokenEstimate} tokens used`">
                            {{ tokenEstimate }}t
                        </span>
                        <button class="ai-ctrl" @click.stop="showHistory = !showHistory" title="Conversation history"
                            aria-label="Toggle history">
                            <i class="fa-solid fa-clock-rotate-left" />
                        </button>
                        <button class="ai-ctrl" @click.stop="isMinimized = !isMinimized"
                            :title="isMinimized ? 'Expand' : 'Minimize'"
                            :aria-label="isMinimized ? 'Expand' : 'Minimize'">
                            <i class="fa-solid fa-minus" />
                        </button>
                        <button class="ai-ctrl" @click.stop="isMaximized = !isMaximized"
                            :title="isMaximized ? 'Restore' : 'Maximize'"
                            :aria-label="isMaximized ? 'Restore' : 'Maximize'">
                            <i :class="isMaximized ? 'fa-solid fa-compress' : 'fa-solid fa-expand'" />
                        </button>
                        <button class="ai-ctrl ai-ctrl--close" @click.stop="$emit('close')" title="Close"
                            aria-label="Close AI panel">
                            <i class="fa-solid fa-xmark" />
                        </button>
                    </div>
                </div>

                <!-- ── Collapsible body ──────────────────────────────────── -->
                <Transition name="ai-collapse">
                    <div v-if="!isMinimized" class="ai-body">

                        <!-- Toolbar: modes + tone + source -->
                        <div class="ai-toolbar">
                            <button v-for="m in MODES" :key="m.id" class="ai-mode-btn"
                                :class="{ active: mode === m.id }" @click="mode = m.id" :title="m.label"
                                :aria-pressed="mode === m.id">
                                <i :class="m.icon" />
                                <span>{{ m.label }}</span>
                            </button>

                            <div class="ai-toolbar-sep" aria-hidden="true" />

                            <select v-model="tone" class="ai-mini-select" aria-label="Tone">
                                <option value="professional">Professional</option>
                                <option value="casual">Casual</option>
                                <option value="persuasive">Persuasive</option>
                                <option value="technical">Technical</option>
                            </select>

                            <select v-model="length" class="ai-mini-select" aria-label="Length">
                                <option value="short">Short</option>
                                <option value="medium">Medium</option>
                                <option value="long">Detailed</option>
                            </select>

                            <div class="ai-toolbar-sep" aria-hidden="true" />

                            <select v-model="source" class="ai-mini-select" aria-label="AI source">
                                <option value="local">Local API</option>
                                <option value="pollinations">Pollinations</option>
                            </select>

                            <button class="ai-icon-btn" @click="clearChat" title="Clear conversation"
                                aria-label="Clear conversation">
                                <i class="fa-solid fa-broom" />
                            </button>
                            <button class="ai-icon-btn" @click="exportChat" title="Export chat"
                                aria-label="Export conversation">
                                <i class="fa-solid fa-download" />
                            </button>
                        </div>

                        <!-- History panel -->
                        <Transition name="ai-collapse">
                            <div v-if="showHistory" class="ai-history" role="region" aria-label="Chat history">
                                <div class="ai-history-header">Recent Conversations</div>
                                <div v-if="chatHistory.length" class="ai-history-list">
                                    <div v-for="(h, hi) in chatHistory" :key="hi" class="ai-history-item"
                                        @click="loadHistory(hi)" role="button"
                                        :aria-label="`Load conversation: ${h.preview}`">
                                        <span class="ai-history-preview">{{ h.preview }}</span>
                                        <span class="ai-history-date">{{ h.date }}</span>
                                        <button class="ai-history-del" @click.stop="deleteHistory(hi)"
                                            aria-label="Delete this conversation">
                                            <i class="fa-solid fa-xmark" />
                                        </button>
                                    </div>
                                </div>
                                <div v-else class="ai-history-empty">No saved conversations</div>
                            </div>
                        </Transition>

                        <!-- Messages -->
                        <div class="ai-messages" ref="messagesRef" role="log" aria-live="polite"
                            aria-label="AI conversation">

                            <!-- Welcome screen (no messages) -->
                            <Transition name="ai-fade">
                                <div v-if="!messages.length && !showHistory" class="ai-welcome">
                                    <div class="ai-welcome-icon" aria-hidden="true">
                                        <div class="ai-welcome-grid">
                                            <span v-for="n in 9" :key="n" :style="`--i:${n}`" />
                                        </div>
                                        <i class="fa-solid fa-wand-magic-sparkles ai-welcome-wand" />
                                    </div>
                                    <h3>What can I help you build?</h3>
                                    <p>Generate content, charts, headlines, or enhance existing text for your report.
                                    </p>
                                    <div class="ai-chips" role="list">
                                        <button v-for="s in SUGGESTIONS" :key="s.text" class="ai-chip"
                                            @click="useSuggestion(s)" role="listitem"
                                            :aria-label="`Quick prompt: ${s.text}`">
                                            <span class="ai-chip-emoji" aria-hidden="true">{{ s.emoji }}</span>
                                            <span>{{ s.text }}</span>
                                        </button>
                                    </div>
                                </div>
                            </Transition>

                            <!-- Message list -->
                            <TransitionGroup name="ai-msg" tag="div" class="ai-msg-list">
                                <div v-for="msg in messages" :key="msg.id" class="ai-msg-wrap"
                                    :class="msg.role === 'user' ? 'ai-msg-wrap--user' : 'ai-msg-wrap--ai'">
                                    <div class="ai-avatar"
                                        :class="msg.role === 'user' ? 'ai-avatar--user' : 'ai-avatar--ai'"
                                        aria-hidden="true">
                                        <i
                                            :class="msg.role === 'user' ? 'fa-solid fa-user' : 'fa-solid fa-wand-magic-sparkles'" />
                                    </div>
                                    <div class="ai-msg-body">
                                        <div class="ai-bubble"
                                            :class="msg.role === 'user' ? 'ai-bubble--user' : 'ai-bubble--ai'"
                                            v-html="renderMd(msg.content)" />

                                        <!-- AI message actions -->
                                        <div v-if="msg.role === 'assistant'" class="ai-msg-actions">
                                            <button v-for="action in (msg.actions || [])" :key="action.label"
                                                class="ai-action-pill"
                                                :class="action.primary && 'ai-action-pill--primary'"
                                                @click="handleAction(action)" :aria-label="action.label">
                                                <i :class="action.icon" aria-hidden="true" />
                                                {{ action.label }}
                                            </button>

                                            <div class="ai-feedback" aria-label="Rate this response">
                                                <button class="ai-feedback-btn"
                                                    :class="msg.feedback === 'up' && 'active'" @click="rate(msg, 'up')"
                                                    title="Helpful" aria-label="Mark as helpful">👍</button>
                                                <button class="ai-feedback-btn"
                                                    :class="msg.feedback === 'down' && 'active'"
                                                    @click="rate(msg, 'down')" title="Not helpful"
                                                    aria-label="Mark as not helpful">👎</button>
                                                <button class="ai-feedback-btn" @click="regenerate(msg)"
                                                    title="Regenerate" aria-label="Regenerate response">
                                                    🔄
                                                </button>
                                            </div>
                                        </div>
                                        <div class="ai-msg-time" aria-label="Sent at">{{ msg.time }}</div>
                                    </div>
                                </div>
                            </TransitionGroup>

                            <!-- Typing indicator -->
                            <div v-if="isLoading" class="ai-msg-wrap ai-msg-wrap--ai" aria-label="AI is thinking">
                                <div class="ai-avatar ai-avatar--ai" aria-hidden="true"><i
                                        class="fa-solid fa-wand-magic-sparkles" /></div>
                                <div class="ai-msg-body">
                                    <div class="ai-bubble ai-bubble--ai ai-bubble--typing" aria-label="Typing">
                                        <span class="ai-dots"><span /><span /><span /></span>
                                        <span class="ai-thinking-label">{{ thinkingLabel }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Input area -->
                        <div class="ai-input-area">
                            <!-- Quick prompts row -->
                            <div v-if="QUICK_PROMPTS[mode]" class="ai-quick-row" aria-label="Quick prompts">
                                <button v-for="q in QUICK_PROMPTS[mode]" :key="q" class="ai-quick-pill"
                                    @click="prompt = q" :aria-label="`Use quick prompt: ${q}`">{{ q }}</button>
                            </div>

                            <div class="ai-input-box" :class="focused && 'ai-input-box--focused'">
                                <div class="ai-mode-stripe" :class="`ai-stripe--${mode}`" aria-hidden="true" />
                                <textarea ref="textareaRef" v-model="prompt" class="ai-textarea"
                                    :placeholder="PLACEHOLDERS[mode]" rows="3" maxlength="1500"
                                    :aria-label="`AI prompt — ${PLACEHOLDERS[mode]}`"
                                    @keydown.enter.exact.prevent="send" @keydown.enter.shift.exact="prompt += '\n'"
                                    @focus="focused = true" @blur="focused = false" @input="growTextarea" />
                                <div class="ai-input-footer">
                                    <span class="ai-char-count" :class="prompt.length > 1200 && 'warn'">
                                        {{ prompt.length }}/1500
                                    </span>
                                    <div class="ai-input-btns">
                                        <button class="ai-attach-btn" @click="startVoice"
                                            :class="isRecording && 'recording'"
                                            :title="isRecording ? 'Stop recording' : 'Voice input'"
                                            :aria-label="isRecording ? 'Stop voice input' : 'Start voice input'">
                                            <i
                                                :class="isRecording ? 'fa-solid fa-microphone' : 'fa-solid fa-microphone-lines'" />
                                        </button>
                                        <button class="ai-attach-btn" @click="attachCtx" title="Attach canvas context"
                                            aria-label="Add canvas context to prompt">
                                            <i class="fa-solid fa-paperclip" />
                                        </button>
                                        <button class="ai-send-btn"
                                            :class="prompt.trim() && !isLoading && 'ai-send-btn--ready'"
                                            :disabled="!prompt.trim() || isLoading" @click="send"
                                            :aria-label="isLoading ? 'AI is generating' : 'Send message'">
                                            <i v-if="!isLoading" class="fa-solid fa-paper-plane" />
                                            <i v-else class="fa-solid fa-spinner fa-spin" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="ai-hint-row" aria-hidden="true">
                                <span><kbd>↵</kbd> Send</span>
                                <span><kbd>⇧↵</kbd> New line</span>
                                <span class="ai-model-label">{{ source === 'pollinations' ? 'Pollinations AI' : 'Local API' }}</span>
                            </div>
                        </div>
                    </div>
                </Transition>

                <!-- Resize handle -->
                <div v-if="!isMinimized && !isMaximized" class="ai-resize-handle" @mousedown.stop="startResize"
                    aria-hidden="true" />
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, reactive, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    visible: { type: Boolean, default: true },
    report: { type: Object, required: true },
    isDark: { type: Boolean, default: false },
    selectedElement: { type: Object, default: null },
})

const emit = defineEmits(['close', 'insert-content', 'insert-chart'])

// ── Refs ───────────────────────────────────────────────────────────────
const panelRef = ref(null)
const messagesRef = ref(null)
const textareaRef = ref(null)

// ── Panel state ────────────────────────────────────────────────────────
const pos = reactive({ x: 0, y: 0 })
const size = reactive({ w: 420, h: 580 })
const isMaximized = ref(false)
const isMinimized = ref(false)
const showHistory = ref(false)
const focused = ref(false)

// ── Chat state ─────────────────────────────────────────────────────────
const messages = ref([])
const prompt = ref('')
const isLoading = ref(false)
const isRecording = ref(false)
const mode = ref('generate')
const tone = ref('professional')
const length = ref('medium')
const source = ref('local')
const chatHistory = ref([])
let msgId = 0
let thinkTimer = null

const THINKING_LABELS = ['Thinking…', 'Generating…', 'Crafting…', 'Analyzing…', 'Writing…']
const thinkingLabel = ref(THINKING_LABELS[0])

const tokenEstimate = computed(() =>
    Math.ceil((messages.value.reduce((s, m) => s + m.content.length, 0) + prompt.value.length) / 4)
)

// ── Constants ──────────────────────────────────────────────────────────
const MODES = [
    { id: 'generate', label: 'Generate', icon: 'fa-solid fa-star' },
    { id: 'enhance', label: 'Enhance', icon: 'fa-solid fa-chart-line' },
    { id: 'summarize', label: 'Summarize', icon: 'fa-solid fa-align-left' },
    { id: 'chart', label: 'Chart', icon: 'fa-solid fa-chart-bar' },
    { id: 'translate', label: 'Translate', icon: 'fa-solid fa-language' },
]

const PLACEHOLDERS = {
    generate: '✦ Describe the content you want to create…',
    enhance: '✦ Paste text to improve professionally…',
    summarize: '✦ Paste text to summarize into key points…',
    chart: '✦ Describe the data you want to visualize…',
    translate: '✦ Paste text and specify the target language…',
}

const QUICK_PROMPTS = {
    generate: ['Executive summary', 'Key findings', 'Introduction', 'Conclusion'],
    enhance: ['Make professional', 'Fix grammar', 'More persuasive', 'Simplify'],
    summarize: ['Bullet points', '3 sentences', 'Key metrics', 'TL;DR'],
    chart: ['Q4 revenue data', 'User growth 6 months', 'Market share pie', 'Sales vs target'],
    translate: ['To Spanish', 'To French', 'To Arabic', 'To German'],
}

const SUGGESTIONS = [
    { emoji: '📊', text: 'Q4 business report executive summary', mode: 'generate' },
    { emoji: '📈', text: 'Revenue trend chart for last 6 months', mode: 'chart' },
    { emoji: '✍️', text: 'Professional headline about market growth', mode: 'generate' },
    { emoji: '🔍', text: 'Summarize KPIs into 3 bullet points', mode: 'summarize' },
    { emoji: '⚡', text: 'Enhance this paragraph professionally', mode: 'enhance' },
    
]

// ── Panel position ─────────────────────────────────────────────────────
const panelStyle = computed(() => {
    if (isMaximized.value) return { inset: '16px', width: 'auto', height: 'auto' }
    return {
        transform: `translate(${pos.x}px, ${pos.y}px)`,
        width: size.w + 'px',
        height: isMinimized.value ? 'auto' : size.h + 'px',
    }
})

// Drag
let dragging = false, dragOff = { x: 0, y: 0 }
function startDrag(e) {
    if (isMaximized.value || e.target.closest('.ai-ctrl')) return
    dragging = true
    dragOff = { x: e.clientX - pos.x, y: e.clientY - pos.y }
    document.addEventListener('mousemove', onDrag)
    document.addEventListener('mouseup', stopDrag)
}
function onDrag(e) {
    if (!dragging) return
    pos.x = Math.max(0, Math.min(e.clientX - dragOff.x, window.innerWidth - size.w))
    pos.y = Math.max(0, Math.min(e.clientY - dragOff.y, window.innerHeight - 60))
}
function stopDrag() {
    dragging = false
    document.removeEventListener('mousemove', onDrag)
    document.removeEventListener('mouseup', stopDrag)
}

// Touch drag
let tDragging = false, tOff = { x: 0, y: 0 }
function startDragTouch(e) {
    if (isMaximized.value) return
    tDragging = true
    const t = e.touches[0]
    tOff = { x: t.clientX - pos.x, y: t.clientY - pos.y }
    document.addEventListener('touchmove', onDragTouch, { passive: false })
    document.addEventListener('touchend', stopDragTouch)
}
function onDragTouch(e) {
    e.preventDefault()
    if (!tDragging) return
    const t = e.touches[0]
    pos.x = Math.max(0, t.clientX - tOff.x)
    pos.y = Math.max(0, t.clientY - tOff.y)
}
function stopDragTouch() {
    tDragging = false
    document.removeEventListener('touchmove', onDragTouch)
    document.removeEventListener('touchend', stopDragTouch)
}

// Resize
let resizing = false, rOff = { x: 0, y: 0, w: 0, h: 0 }
function startResize(e) {
    resizing = true
    rOff = { x: e.clientX, y: e.clientY, w: size.w, h: size.h }
    document.addEventListener('mousemove', onResize)
    document.addEventListener('mouseup', stopResize)
}
function onResize(e) {
    if (!resizing) return
    size.w = Math.max(300, rOff.w + (e.clientX - rOff.x))
    size.h = Math.max(360, rOff.h + (e.clientY - rOff.y))
}
function stopResize() {
    resizing = false
    document.removeEventListener('mousemove', onResize)
    document.removeEventListener('mouseup', stopResize)
}

// ── Markdown renderer ──────────────────────────────────────────────────
function renderMd(text) {
    return text
        .replace(/```(\w*)\n?([\s\S]*?)```/g, (_, lang, code) =>
            `<pre class="ai-code"><code>${esc(code.trim())}</code></pre>`)
        .replace(/`([^`]+)`/g, '<code class="ai-inline-code">$1</code>')
        .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
        .replace(/\*([^*]+)\*/g, '<em>$1</em>')
        .replace(/^#{1,3}\s+(.+)/gm, '<p class="ai-md-h">$1</p>')
        .replace(/^[-•]\s+(.+)/gm, '<li>$1</li>')
        .replace(/(<li>[\s\S]+?<\/li>)/g, '<ul class="ai-md-ul">$1</ul>')
        .replace(/\n{2,}/g, '</p><p>')
        .replace(/\n/g, '<br>')
}

function esc(s) {
    return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
}

// ── Send message ───────────────────────────────────────────────────────
async function send() {
    const text = prompt.value.trim()
    if (!text || isLoading.value) return

    messages.value.push({ id: ++msgId, role: 'user', content: text, time: now() })
    prompt.value = ''
    isLoading.value = true
    let li = 0
    thinkTimer = setInterval(() => {
        li = (li + 1) % THINKING_LABELS.length
        thinkingLabel.value = THINKING_LABELS[li]
    }, 1100)
    scrollBottom()

    try {
        let result = ''
        let chartData = null
        const apiType = { generate: 'text', enhance: 'text', summarize: 'summary', chart: 'chart_data', translate: 'text' }[mode.value] || 'text'

        if (source.value === 'pollinations') {
            const sysPrompt = `You are a ${mode.value} AI for business reports. Tone: ${tone.value}. Length: ${length.value}.`
            const res = await fetch(
                `https://text.pollinations.ai/${encodeURIComponent(`${sysPrompt}\n\nUser: ${text}`)}`,
                { signal: AbortSignal.timeout(28000) }
            )
            if (res.ok) result = await res.text()
            else throw new Error('Pollinations failed')
        } else {
            const res = await fetch('/api/ai/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrf(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ prompt: text, type: apiType, tone: tone.value, length: length.value }),
                signal: AbortSignal.timeout(18000),
            })
            const data = await res.json()
            if (apiType === 'chart_data') {
                chartData = data
                result = buildChartPreview(data)
            } else {
                result = data.result || data.enhanced || data.summary || 'No response.'
            }
        }

        clearInterval(thinkTimer)
        const actions = []

        if (chartData) {
            actions.push({ label: 'Insert Chart', icon: 'fa-solid fa-chart-bar', action: 'insert-chart', data: chartData, primary: true })
        } else {
            actions.push({ label: 'Insert', icon: 'fa-solid fa-plus', action: 'insert-text', data: result, primary: true })
            actions.push({ label: 'Copy', icon: 'fa-solid fa-copy', action: 'copy', data: result })
        }

        messages.value.push({ id: ++msgId, role: 'assistant', content: result, actions, time: now(), feedback: null })
        saveHistory()
    } catch (err) {
        clearInterval(thinkTimer)
        messages.value.push({
            id: ++msgId, role: 'assistant',
            content: err.name === 'TimeoutError' ? '⏱️ Request timed out. Try a shorter prompt.' : '❌ Connection failed. Please try again.',
            actions: [], time: now(), feedback: null,
        })
    }

    isLoading.value = false
    scrollBottom()
}

function buildChartPreview(d) {
    return `**Chart Generated!**\n\n**Title:** ${d.title || 'Chart'}\n**Type:** ${d.suggested_chart_type || 'bar-chart'}\n**Labels:** ${(d.labels || []).join(', ')}\n**Values:** ${(d.values || []).join(', ')}\n\n${d.insights || ''}`
}

// ── Actions ────────────────────────────────────────────────────────────
function handleAction(action) {
    if (action.action === 'insert-text') {
        emit('insert-content', { type: 'text', content: action.data })
    } else if (action.action === 'insert-chart') {
        emit('insert-chart', action.data)
    } else if (action.action === 'copy') {
        navigator.clipboard.writeText(stripMd(action.data))
        // brief feedback via toast (parent handles)
    }
}

function stripMd(t) {
    return t.replace(/```[\s\S]*?```/g, m => m.slice(3, -3)).replace(/[`*#_~]/g, '')
}

function rate(msg, r) { msg.feedback = r }

function regenerate(msg) {
    const userMsg = [...messages.value].reverse().find(m => m.role === 'user')
    if (userMsg) { prompt.value = userMsg.content; send() }
}

// ── Chat history ───────────────────────────────────────────────────────
function saveHistory() {
    if (messages.value.length < 2) return
    const preview = messages.value[0]?.content.replace(/<[^>]*>/g, '').trim().substring(0, 50) || 'Conversation'
    const entry = { preview, date: new Date().toLocaleDateString(), messages: JSON.parse(JSON.stringify(messages.value)) }
    chatHistory.value = [entry, ...chatHistory.value.filter(h => h.preview !== preview)].slice(0, 20)
    localStorage.setItem('ai_chat_history', JSON.stringify(chatHistory.value))
}

function loadHistory(i) {
    messages.value = JSON.parse(JSON.stringify(chatHistory.value[i].messages))
    showHistory.value = false
    scrollBottom()
}

function deleteHistory(i) {
    chatHistory.value.splice(i, 1)
    localStorage.setItem('ai_chat_history', JSON.stringify(chatHistory.value))
}

function loadChatHistory() {
    try { chatHistory.value = JSON.parse(localStorage.getItem('ai_chat_history') || '[]') } catch { }
}

// ── Helpers ────────────────────────────────────────────────────────────
function useSuggestion(s) { mode.value = s.mode; prompt.value = s.text; nextTick(() => textareaRef.value?.focus()) }
function clearChat() { messages.value = [] }
function exportChat() {
    const text = messages.value.map(m => `[${m.role.toUpperCase()}] ${m.time}\n${stripMd(m.content)}`).join('\n\n---\n\n')
    const a = Object.assign(document.createElement('a'), { href: URL.createObjectURL(new Blob([text])), download: 'ai-chat.txt' })
    a.click()
}
function scrollBottom() { nextTick(() => { if (messagesRef.value) messagesRef.value.scrollTop = messagesRef.value.scrollHeight }) }
function now() { return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }
function getCsrf() { return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' }
function growTextarea() {
    const el = textareaRef.value; if (!el) return
    el.style.height = 'auto'
    el.style.height = Math.min(el.scrollHeight, 130) + 'px'
}
function attachCtx() {
    let ctx = props.report?.title ? `[Report: "${props.report.title}"] ` : ''
    if (props.selectedElement?.content) {
        const t = props.selectedElement.content.replace(/<[^>]*>/g, '').trim().substring(0, 400)
        if (t) ctx += `[Selected: "${t}"] `
    }
    prompt.value = ctx + prompt.value
    textareaRef.value?.focus()
}
function startVoice() {
    const SR = window.SpeechRecognition || window.webkitSpeechRecognition
    if (!SR) return
    const r = new SR()
    r.lang = 'en-US'; r.interimResults = false
    isRecording.value = true
    r.start()
    r.onresult = e => { prompt.value += e.results[0][0].transcript; isRecording.value = false }
    r.onerror = r.onend = () => { isRecording.value = false }
}

// ── Init ───────────────────────────────────────────────────────────────
onMounted(() => {
    pos.x = window.innerWidth - size.w - 20
    pos.y = window.innerHeight - size.h - 20
    loadChatHistory()
    nextTick(() => textareaRef.value?.focus())
})

onBeforeUnmount(() => {
    clearInterval(thinkTimer)
    stopDrag(); stopDragTouch(); stopResize()
})
</script>

<style scoped>
/* ── Root variables ─────────────────────────────────────────────────── */
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
    --ai-accent-l: rgba(99, 102, 241, .08);
    --ai-user-bg: #6366f1;
    --ai-shadow: 0 24px 80px rgba(0, 0, 0, .16), 0 4px 20px rgba(0, 0, 0, .08);
    --ai-radius: 18px;

    position: fixed;
    z-index: 9000;
    background: var(--ai-bg);
    border: 1px solid var(--ai-border);
    border-radius: var(--ai-radius);
    box-shadow: var(--ai-shadow);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    will-change: transform;
    user-select: none;
    min-width: 300px;
    min-height: 60px;
    font-family: 'DM Sans', system-ui, sans-serif;
}

.ai-dark {
    --ai-bg: #18181b;
    --ai-bg2: #27272a;
    --ai-bg3: #3f3f46;
    --ai-border: #3f3f46;
    --ai-text: #f4f4f5;
    --ai-text2: #a1a1aa;
    --ai-text3: #71717a;
    --ai-accent: #818cf8;
    --ai-shadow: 0 24px 80px rgba(0, 0, 0, .5);
}

.ai-maximized {
    border-radius: 12px !important;
}

/* top accent bar */
.ai-panel::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--ai-accent), var(--ai-accent2), var(--ai-accent));
    background-size: 200%;
    animation: shimmer 3s linear infinite;
    z-index: 1;
}

@keyframes shimmer {
    from {
        background-position: -200%
    }

    to {
        background-position: 200%
    }
}

/* ── Header ─────────────────────────────────────────────────────────── */
.ai-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-bottom: 1px solid var(--ai-border);
    cursor: grab;
    flex-shrink: 0;
    background: linear-gradient(135deg, var(--ai-accent-l), transparent 60%);
}

.ai-header:active {
    cursor: grabbing;
}

.ai-orb {
    width: 32px;
    height: 32px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--ai-accent), var(--ai-accent2));
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    position: relative;
    flex-shrink: 0;
}

.ai-orb-ring {
    position: absolute;
    inset: -4px;
    border-radius: 14px;
    border: 1px solid var(--ai-accent);
    opacity: 0;
    animation: orbPulse 2.4s ease-out infinite;
}

.ai-orb-icon {
    font-size: 14px;
}

@keyframes orbPulse {
    0% {
        transform: scale(.85);
        opacity: .6
    }

    100% {
        transform: scale(1.4);
        opacity: 0
    }
}

.ai-title-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.ai-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--ai-text);
}

.ai-status {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--ai-text3);
}

.ai-status-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: #22c55e;
    animation: statusPulse 2s ease-in-out infinite;
}

.ai-status--busy .ai-status-dot {
    background: var(--ai-accent);
    animation: spin .8s linear infinite;
}

@keyframes statusPulse {

    0%,
    100% {
        opacity: 1
    }

    50% {
        opacity: .4
    }
}

@keyframes spin {
    to {
        transform: rotate(360deg)
    }
}

.ai-header-controls {
    display: flex;
    align-items: center;
    gap: 2px;
    flex-shrink: 0;
}

.ai-token-count {
    font-size: 9px;
    font-weight: 700;
    color: var(--ai-text3);
    padding: 2px 7px;
    background: var(--ai-bg3);
    border: 1px solid var(--ai-border);
    border-radius: 99px;
    margin-right: 4px;
}

.ai-ctrl {
    width: 26px;
    height: 26px;
    border: none;
    background: transparent;
    border-radius: 7px;
    cursor: pointer;
    color: var(--ai-text3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    transition: all .14s;
}

.ai-ctrl:hover {
    background: var(--ai-bg3);
    color: var(--ai-text);
}

.ai-ctrl--close:hover {
    background: #fee2e2;
    color: #ef4444;
}

/* ── Body ───────────────────────────────────────────────────────────── */
.ai-body {
    display: flex;
    flex-direction: column;
    flex: 1;
    overflow: hidden;
}

/* ── Toolbar ────────────────────────────────────────────────────────── */
.ai-toolbar {
    display: flex;
    align-items: center;
    gap: 2px;
    padding: 7px 8px 5px;
    border-bottom: 1px solid var(--ai-border);
    flex-shrink: 0;
    overflow-x: auto;
    scrollbar-width: none;
}

.ai-toolbar::-webkit-scrollbar {
    display: none;
}

.ai-mode-btn {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border: 1px solid transparent;
    border-radius: 8px;
    background: transparent;
    cursor: pointer;
    font-size: 10px;
    font-weight: 600;
    color: var(--ai-text3);
    white-space: nowrap;
    transition: all .14s;
    font-family: inherit;
    flex-shrink: 0;
}

.ai-mode-btn:hover {
    background: var(--ai-bg3);
    color: var(--ai-text2);
}

.ai-mode-btn.active {
    background: var(--ai-accent-l);
    color: var(--ai-accent);
    border-color: var(--ai-accent);
}

.ai-toolbar-sep {
    width: 1px;
    height: 16px;
    background: var(--ai-border);
    margin: 0 4px;
    flex-shrink: 0;
}

.ai-mini-select {
    appearance: none;
    padding: 3px 20px 3px 8px;
    border: 1px solid var(--ai-border);
    border-radius: 7px;
    background: var(--ai-bg2);
    color: var(--ai-text2);
    font-size: 10px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    outline: none;
    flex-shrink: 0;
}

.ai-mini-select:focus {
    border-color: var(--ai-accent);
}

.ai-icon-btn {
    width: 26px;
    height: 26px;
    border: none;
    background: transparent;
    border-radius: 7px;
    cursor: pointer;
    color: var(--ai-text3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    transition: all .14s;
    flex-shrink: 0;
}

.ai-icon-btn:hover {
    background: var(--ai-bg3);
    color: var(--ai-text);
}

/* ── History ────────────────────────────────────────────────────────── */
.ai-history {
    max-height: 180px;
    overflow-y: auto;
    border-bottom: 1px solid var(--ai-border);
    padding: 8px;
}

.ai-history-header {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--ai-text3);
    margin-bottom: 6px;
}

.ai-history-list {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.ai-history-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 8px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 10px;
    transition: all .1s;
}

.ai-history-item:hover {
    background: var(--ai-bg3);
}

.ai-history-preview {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--ai-text2);
}

.ai-history-date {
    font-size: 9px;
    color: var(--ai-text3);
    flex-shrink: 0;
}

.ai-history-del {
    opacity: 0;
    width: 16px;
    height: 16px;
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--ai-text3);
    font-size: 9px;
    transition: all .1s;
    border-radius: 3px;
}

.ai-history-item:hover .ai-history-del {
    opacity: 1;
}

.ai-history-del:hover {
    color: #ef4444;
}

.ai-history-empty {
    text-align: center;
    padding: 12px;
    font-size: 10px;
    color: var(--ai-text3);
}

/* ── Messages ───────────────────────────────────────────────────────── */
.ai-messages {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    scrollbar-width: thin;
    scrollbar-color: var(--ai-border) transparent;
}

.ai-messages::-webkit-scrollbar {
    width: 3px;
}

.ai-messages::-webkit-scrollbar-thumb {
    background: var(--ai-border);
    border-radius: 99px;
}

/* Welcome */
.ai-welcome {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 12px 8px;
    gap: 10px;
}

.ai-welcome-icon {
    position: relative;
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ai-welcome-grid {
    position: absolute;
    inset: 0;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 3px;
}

.ai-welcome-grid span {
    background: var(--ai-accent);
    border-radius: 4px;
    opacity: 0;
    animation: cellFade .6s ease forwards;
    animation-delay: calc(var(--i)*.05s);
}

@keyframes cellFade {
    from {
        opacity: 0;
        transform: scale(.5)
    }

    to {
        opacity: .08;
        transform: scale(1)
    }
}

.ai-welcome-wand {
    position: relative;
    z-index: 1;
    font-size: 24px;
    color: var(--ai-accent);
}

.ai-welcome h3 {
    font-size: 14px;
    font-weight: 700;
    color: var(--ai-text);
    margin: 0;
}

.ai-welcome p {
    font-size: 11px;
    color: var(--ai-text3);
    line-height: 1.5;
    margin: 0;
}

.ai-chips {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 5px;
    width: 100%;
}

.ai-chip {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 7px 10px;
    border: 1px solid var(--ai-border);
    border-radius: 10px;
    background: var(--ai-bg2);
    cursor: pointer;
    font-size: 10px;
    font-weight: 600;
    color: var(--ai-text2);
    text-align: left;
    transition: all .15s;
    font-family: inherit;
}

.ai-chip:hover {
    border-color: var(--ai-accent);
    color: var(--ai-accent);
    background: var(--ai-accent-l);
    transform: translateY(-1px);
}

.ai-chip-emoji {
    font-size: 13px;
    flex-shrink: 0;
}

/* Msg list */
.ai-msg-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.ai-msg-wrap {
    display: flex;
    gap: 8px;
}

.ai-msg-wrap--user {
    flex-direction: row-reverse;
}

.ai-avatar {
    width: 26px;
    height: 26px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    flex-shrink: 0;
    margin-top: 2px;
}

.ai-avatar--user {
    background: var(--ai-user-bg);
    color: #fff;
}

.ai-avatar--ai {
    background: var(--ai-accent-l);
    color: var(--ai-accent);
}

.ai-msg-body {
    display: flex;
    flex-direction: column;
    gap: 4px;
    max-width: 85%;
}

.ai-msg-wrap--user .ai-msg-body {
    align-items: flex-end;
}

.ai-bubble {
    padding: 8px 12px;
    border-radius: 12px;
    font-size: 12px;
    line-height: 1.6;
    word-break: break-word;
}

.ai-bubble--user {
    background: var(--ai-user-bg);
    color: #fff;
    border-radius: 12px 12px 3px 12px;
}

.ai-bubble--ai {
    background: var(--ai-bg2);
    color: var(--ai-text);
    border: 1px solid var(--ai-border);
    border-radius: 12px 12px 12px 3px;
}

.ai-bubble--typing {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
}

:deep(.ai-bubble .ai-code) {
    background: #1e293b;
    color: #34d399;
    padding: 10px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-family: monospace;
    overflow-x: auto;
    margin: 6px 0;
}

:deep(.ai-bubble .ai-inline-code) {
    background: rgba(99, 102, 241, .12);
    color: var(--ai-accent);
    padding: 1px 5px;
    border-radius: 4px;
    font-size: 11px;
}

:deep(.ai-bubble .ai-md-h) {
    font-weight: 700;
    font-size: 13px;
    color: var(--ai-accent);
    margin: 4px 0 2px;
}

:deep(.ai-bubble .ai-md-ul) {
    padding-left: 14px;
    margin: 4px 0;
}

:deep(.ai-bubble li) {
    font-size: 11.5px;
    margin: 2px 0;
}

:deep(.ai-bubble strong) {
    font-weight: 700;
}

/* Typing dots */
.ai-dots {
    display: flex;
    gap: 4px;
}

.ai-dots span {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: var(--ai-accent);
    animation: typBounce 1.2s ease-in-out infinite;
}

.ai-dots span:nth-child(2) {
    animation-delay: .2s;
}

.ai-dots span:nth-child(3) {
    animation-delay: .4s;
}

@keyframes typBounce {

    0%,
    60%,
    100% {
        transform: translateY(0);
        opacity: .5
    }

    30% {
        transform: translateY(-5px);
        opacity: 1
    }
}

.ai-thinking-label {
    font-size: 10px;
    color: var(--ai-text3);
    font-style: italic;
}

/* Msg actions */
.ai-msg-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 4px;
    margin-top: 4px;
}

.ai-action-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border: 1px solid var(--ai-border);
    border-radius: 99px;
    background: var(--ai-bg);
    color: var(--ai-accent);
    font-size: 10px;
    font-weight: 700;
    cursor: pointer;
    transition: all .14s;
    font-family: inherit;
}

.ai-action-pill--primary {
    background: var(--ai-accent-l);
    border-color: var(--ai-accent);
}

.ai-action-pill:hover {
    background: var(--ai-accent);
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, .3);
}

.ai-feedback {
    display: flex;
    gap: 3px;
}

.ai-feedback-btn {
    width: 24px;
    height: 24px;
    border: 1px solid var(--ai-border);
    border-radius: 6px;
    background: transparent;
    cursor: pointer;
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all .1s;
}

.ai-feedback-btn:hover {
    background: var(--ai-bg3);
}

.ai-feedback-btn.active {
    background: var(--ai-accent-l);
    border-color: var(--ai-accent);
}

.ai-msg-time {
    font-size: 9px;
    color: var(--ai-text3);
    padding: 0 2px;
}

/* ── Input area ─────────────────────────────────────────────────────── */
.ai-input-area {
    border-top: 1px solid var(--ai-border);
    padding: 8px 10px 10px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex-shrink: 0;
}

.ai-quick-row {
    display: flex;
    gap: 4px;
    overflow-x: auto;
    scrollbar-width: none;
    padding-bottom: 2px;
}

.ai-quick-row::-webkit-scrollbar {
    display: none;
}

.ai-quick-pill {
    white-space: nowrap;
    padding: 3px 9px;
    border: 1px solid var(--ai-border);
    border-radius: 99px;
    background: var(--ai-bg2);
    color: var(--ai-text3);
    font-size: 9px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    transition: all .14s;
    flex-shrink: 0;
}

.ai-quick-pill:hover {
    border-color: var(--ai-accent);
    color: var(--ai-accent);
    background: var(--ai-accent-l);
}

.ai-input-box {
    border: 1px solid var(--ai-border);
    border-radius: 12px;
    background: var(--ai-bg2);
    overflow: hidden;
    transition: border-color .15s, box-shadow .15s;
    position: relative;
}

.ai-input-box--focused {
    border-color: var(--ai-accent);
    box-shadow: 0 0 0 3px var(--ai-accent-l);
}

.ai-mode-stripe {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    border-radius: 12px 12px 0 0;
}

.ai-stripe--generate {
    background: linear-gradient(90deg, #6366f1, #8b5cf6);
}

.ai-stripe--enhance {
    background: linear-gradient(90deg, #10b981, #06b6d4);
}

.ai-stripe--summarize {
    background: linear-gradient(90deg, #f59e0b, #ef4444);
}

.ai-stripe--chart {
    background: linear-gradient(90deg, #3b82f6, #06b6d4);
}

.ai-stripe--translate {
    background: linear-gradient(90deg, #ec4899, #8b5cf6);
}

.ai-textarea {
    width: 100%;
    padding: 10px 12px 6px;
    background: transparent;
    border: none;
    outline: none;
    resize: none;
    color: var(--ai-text);
    font-size: 12px;
    line-height: 1.5;
    font-family: inherit;
    min-height: 50px;
    max-height: 130px;
    box-sizing: border-box;
}

.ai-textarea::placeholder {
    color: var(--ai-text3);
}

.ai-input-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 4px 10px 8px;
}

.ai-char-count {
    font-size: 9px;
    color: var(--ai-text3);
    font-weight: 600;
}

.ai-char-count.warn {
    color: #ef4444;
}

.ai-input-btns {
    display: flex;
    gap: 5px;
    align-items: center;
}

.ai-attach-btn {
    width: 28px;
    height: 28px;
    border: none;
    background: var(--ai-bg3);
    border-radius: 8px;
    color: var(--ai-text3);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    transition: all .14s;
}

.ai-attach-btn:hover {
    background: var(--ai-accent-l);
    color: var(--ai-accent);
}

.ai-attach-btn.recording {
    background: #ef4444 !important;
    color: #fff !important;
    animation: recPulse 1s ease-in-out infinite;
}

@keyframes recPulse {

    0%,
    100% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, .4)
    }

    50% {
        box-shadow: 0 0 0 8px rgba(239, 68, 68, 0)
    }
}

.ai-send-btn {
    width: 30px;
    height: 30px;
    border: none;
    background: var(--ai-bg3);
    border-radius: 9px;
    color: var(--ai-text3);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    transition: all .15s;
}

.ai-send-btn--ready {
    background: var(--ai-accent);
    color: #fff;
    box-shadow: 0 4px 12px rgba(99, 102, 241, .35);
}

.ai-send-btn--ready:hover {
    transform: scale(1.07);
    box-shadow: 0 6px 20px rgba(99, 102, 241, .45);
}

.ai-send-btn:disabled {
    opacity: .4;
    cursor: not-allowed;
}

.ai-hint-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 9px;
    color: var(--ai-text3);
}

.ai-hint-row kbd {
    padding: 1px 4px;
    border-radius: 3px;
    background: var(--ai-bg3);
    border: 1px solid var(--ai-border);
    font-family: inherit;
    font-size: 9px;
}

.ai-model-label {
    margin-left: auto;
    font-weight: 700;
    font-size: 9px;
    background: linear-gradient(90deg, var(--ai-accent), var(--ai-accent2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* ── Resize handle ──────────────────────────────────────────────────── */
.ai-resize-handle {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 18px;
    height: 18px;
    cursor: se-resize;
    background: linear-gradient(135deg, transparent 50%, var(--ai-border) 50%);
    border-radius: 0 0 18px 0;
}

/* ── Transitions ────────────────────────────────────────────────────── */
.ai-emerge-enter-active {
    animation: aiEmerge .35s cubic-bezier(.16, 1, .3, 1);
}

.ai-emerge-leave-active {
    animation: aiEmerge .2s ease reverse;
}

@keyframes aiEmerge {
    from {
        opacity: 0;
        transform: translateY(20px) scale(.94)
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1)
    }
}

.ai-collapse-enter-active {
    transition: all .28s cubic-bezier(.16, 1, .3, 1);
}

.ai-collapse-leave-active {
    transition: all .18s ease;
}

.ai-collapse-enter-from,
.ai-collapse-leave-to {
    opacity: 0;
    transform: translateY(-8px);
    max-height: 0;
}

.ai-fade-enter-active {
    transition: opacity .3s, transform .3s;
}

.ai-fade-leave-active {
    transition: opacity .2s;
}

.ai-fade-enter-from,
.ai-fade-leave-to {
    opacity: 0;
    transform: translateY(6px);
}

.ai-msg-enter-active {
    transition: all .28s cubic-bezier(.16, 1, .3, 1);
}

.ai-msg-enter-from {
    opacity: 0;
    transform: translateY(10px) scale(.97);
}

/* ── Mobile ─────────────────────────────────────────────────────────── */
@media (max-width: 520px) {
    .ai-panel {
        bottom: 0 !important;
        right: 0 !important;
        left: 0 !important;
        width: 100% !important;
        transform: none !important;
        border-radius: 18px 18px 0 0;
        min-width: unset;
        max-height: 70vh;
    }

    .ai-chips {
        grid-template-columns: 1fr;
    }

    .ai-header {
        cursor: default;
    }

    .ai-resize-handle {
        display: none;
    }
}
</style>