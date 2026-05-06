<!--
  ╔══════════════════════════════════════════════════════════════════╗
  ║   AiPanel - AI Assistant Panel                                   ║
  ╚══════════════════════════════════════════════════════════════════╝
-->
<template>
  <Teleport to="body">
    <Transition name="ai-slide">
      <div v-if="visible" class="ai-panel">
        <div class="ai-header">
          <div class="ai-title">
            <i class="fa-solid fa-wand-magic-sparkles ai-glow"></i>
            <span>AI Assistant</span>
            <span class="ai-badge">BETA</span>
          </div>
          <div class="ai-header-actions">
            <button class="ai-header-btn" @click="clearChat" title="Clear Chat">
              <i class="fa-solid fa-broom"></i>
            </button>
            <button class="ai-header-btn" @click="$emit('close')" title="Close">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
        </div>

        <!-- Chat Messages -->
        <div class="ai-messages" ref="messagesEl">
          <div v-if="messages.length === 0" class="ai-welcome">
            <div class="ai-welcome-icon">
              <i class="fa-solid fa-robot"></i>
            </div>
            <h4>AI Report Assistant</h4>
            <p>I can help you generate content, suggest headlines, create chart data, and more.</p>
            <div class="ai-suggestions">
              <button
                v-for="sug in suggestions"
                :key="sug"
                class="ai-suggestion-chip"
                @click="prompt = sug; sendMessage()"
              >
                {{ sug }}
              </button>
            </div>
          </div>

          <div
            v-for="(msg, idx) in messages"
            :key="idx"
            class="ai-message"
            :class="msg.role"
          >
            <div class="ai-avatar">
              <i v-if="msg.role === 'assistant'" class="fa-solid fa-robot"></i>
              <i v-else class="fa-solid fa-user"></i>
            </div>
            <div class="ai-bubble">
              <div class="ai-bubble-content" v-html="formatMessage(msg.content)"></div>
              <div v-if="msg.role === 'assistant' && msg.actions" class="ai-bubble-actions">
                <button
                  v-for="action in msg.actions"
                  :key="action.label"
                  class="ai-action-btn"
                  @click="handleAction(action)"
                >
                  <i :class="action.icon"></i>
                  {{ action.label }}
                </button>
              </div>
            </div>
          </div>

          <!-- Typing Indicator -->
          <div v-if="isLoading" class="ai-message assistant">
            <div class="ai-avatar">
              <i class="fa-solid fa-robot"></i>
            </div>
            <div class="ai-bubble typing">
              <span class="typing-dot"></span>
              <span class="typing-dot"></span>
              <span class="typing-dot"></span>
            </div>
          </div>
        </div>

        <!-- Input Area -->
        <div class="ai-input-area">
          <div class="ai-mode-tabs">
            <button
              v-for="mode in modes"
              :key="mode.value"
              class="ai-mode-tab"
              :class="{ active: selectedMode === mode.value }"
              @click="selectedMode = mode.value"
            >
              <i :class="mode.icon"></i>
              <span>{{ mode.label }}</span>
            </button>
          </div>
          <div class="ai-input-row">
            <textarea
              v-model="prompt"
              class="ai-textarea"
              :placeholder="getPlaceholder()"
              rows="2"
              @keydown.enter.exact.prevent="sendMessage"
              @keydown.enter.shift.exact="prompt += '\n'"
              ref="textareaEl"
            ></textarea>
            <button
              class="ai-send-btn"
              @click="sendMessage"
              :disabled="!prompt.trim() || isLoading"
            >
              <i v-if="isLoading" class="fa-solid fa-spinner fa-spin"></i>
              <i v-else class="fa-solid fa-paper-plane"></i>
            </button>
          </div>
          <div class="ai-input-hint">
            Press <kbd>Enter</kbd> to send, <kbd>Shift+Enter</kbd> for new line
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, nextTick, watch, onMounted } from 'vue'

const props = defineProps({
  visible: { type: Boolean, default: true },
  report: { type: Object, required: true },
})

const emit = defineEmits(['close', 'insert-content', 'insert-chart'])

// State
const prompt = ref('')
const messages = ref([])
const isLoading = ref(false)
const selectedMode = ref('generate')
const messagesEl = ref(null)
const textareaEl = ref(null)

const modes = [
  { label: 'Generate', value: 'generate', icon: 'fa-solid fa-wand-magic-sparkles' },
  { label: 'Enhance', value: 'enhance', icon: 'fa-solid fa-pen-to-square' },
  { label: 'Summarize', value: 'summarize', icon: 'fa-solid fa-compress' },
  { label: 'Chart', value: 'chart', icon: 'fa-solid fa-chart-pie' },
]

const suggestions = [
  'Write an executive summary for a Q4 business report',
  'Generate revenue chart data for the last 6 months',
  'Create a headline about market growth',
  'Write a professional conclusion paragraph',
  'Generate KPI metrics for a sales report',
]

function getPlaceholder() {
  const placeholders = {
    generate: 'Describe what you want to create...',
    enhance: 'Paste text to improve professionally...',
    summarize: 'Paste long text to summarize...',
    chart: 'Describe the chart data you need...',
  }
  return placeholders[selectedMode.value] || 'Ask me anything...'
}

function getCsrf() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
}

function formatMessage(content) {
  // Convert URLs to links
  return content
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    .replace(/`(.*?)`/g, '<code>$1</code>')
    .replace(/\n/g, '<br>')
}

async function sendMessage() {
  const text = prompt.value.trim()
  if (!text || isLoading.value) return

  // Add user message
  messages.value.push({ role: 'user', content: text })
  prompt.value = ''
  isLoading.value = true

  try {
    // Map mode to API type
    const typeMap = {
      generate: 'text',
      enhance: 'text',
      summarize: 'summary',
      chart: 'chart_data',
    }

    const response = await fetch('/api/ai/generate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrf(),
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        prompt: text,
        type: typeMap[selectedMode.value] || 'text',
      }),
    })

    const data = await response.json()

    if (data.error) {
      messages.value.push({ role: 'assistant', content: 'Sorry, I encountered an error. Please try again.' })
    } else if (selectedMode.value === 'chart') {
      // Chart data response
      const content = `Here's your chart data:\n\n**Title:** ${data.title || 'Chart'}\n**Labels:** ${(data.labels || []).join(', ')}\n**Values:** ${(data.values || []).join(', ')}\n**Suggested Type:** ${data.suggested_chart_type || 'bar-chart'}`
      messages.value.push({
        role: 'assistant',
        content,
        actions: [
          { label: 'Insert Chart', icon: 'fa-solid fa-chart-pie', action: 'insert-chart', data },
        ],
      })
    } else {
      const result = data.result || data.enhanced || 'No response generated.'
      messages.value.push({
        role: 'assistant',
        content: result,
        actions: [
          { label: 'Insert to Canvas', icon: 'fa-solid fa-plus', action: 'insert-text', data: result },
          { label: 'Copy', icon: 'fa-solid fa-copy', action: 'copy', data: result },
        ],
      })
    }
  } catch (err) {
    messages.value.push({ role: 'assistant', content: 'Failed to connect to AI service. Please try again.' })
  }

  isLoading.value = false
  scrollToBottom()
}

function handleAction(action) {
  if (action.action === 'insert-text') {
    emit('insert-content', action.data)
  } else if (action.action === 'insert-chart') {
    emit('insert-chart', action.data)
  } else if (action.action === 'copy') {
    navigator.clipboard.writeText(action.data)
  }
}

function clearChat() {
  messages.value = []
}

function scrollToBottom() {
  nextTick(() => {
    if (messagesEl.value) {
      messagesEl.value.scrollTop = messagesEl.value.scrollHeight
    }
  })
}

watch(() => props.visible, (val) => {
  if (val) {
    nextTick(() => textareaEl.value?.focus())
  }
})

onMounted(() => {
  nextTick(() => textareaEl.value?.focus())
})
</script>

<style scoped>
.ai-panel {
  position: fixed;
  right: 20px;
  bottom: 50px;
  width: 400px;
  max-height: 550px;
  background: var(--bg-panel, #ffffff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2);
  display: flex;
  flex-direction: column;
  z-index: 400;
  overflow: hidden;
}

/* Header */
.ai-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-bottom: 1px solid var(--border, #e2e8f0);
  background: linear-gradient(135deg, var(--accent-light, rgba(99,102,241,0.08)), transparent);
}

.ai-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;
  font-size: 14px;
  color: var(--text-primary, #0f172a);
}

.ai-glow {
  color: var(--accent, #6366f1);
  animation: aiGlow 2s ease-in-out infinite;
}

@keyframes aiGlow {
  0%, 100% { filter: drop-shadow(0 0 4px rgba(99,102,241,0.4)); }
  50% { filter: drop-shadow(0 0 12px rgba(99,102,241,0.8)); }
}

.ai-badge {
  font-size: 8px;
  font-weight: 800;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff;
  padding: 2px 6px;
  border-radius: 99px;
  letter-spacing: 0.05em;
}

.ai-header-actions { display: flex; gap: 4px; }

.ai-header-btn {
  width: 28px;
  height: 28px;
  border: none;
  background: transparent;
  border-radius: 6px;
  cursor: pointer;
  color: var(--text-muted, #94a3b8);
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
}

.ai-header-btn:hover {
  background: var(--bg-secondary, #f8fafc);
  color: var(--text-primary, #0f172a);
}

/* Messages */
.ai-messages {
  flex: 1;
  overflow-y: auto;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  min-height: 150px;
  max-height: 300px;
}

.ai-welcome {
  text-align: center;
  padding: 20px;
}

.ai-welcome-icon {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  background: var(--accent-light, rgba(99,102,241,0.1));
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  color: var(--accent, #6366f1);
  margin: 0 auto 10px;
}

.ai-welcome h4 {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-primary, #0f172a);
  margin-bottom: 4px;
}

.ai-welcome p {
  font-size: 11px;
  color: var(--text-muted, #94a3b8);
  line-height: 1.5;
  margin-bottom: 12px;
}

.ai-suggestions {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  justify-content: center;
}

.ai-suggestion-chip {
  padding: 5px 10px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 99px;
  background: var(--bg-secondary, #f8fafc);
  cursor: pointer;
  font-size: 10px;
  color: var(--text-secondary, #475569);
  transition: all 0.15s;
  font-family: inherit;
}

.ai-suggestion-chip:hover {
  border-color: var(--accent, #6366f1);
  color: var(--accent, #6366f1);
  background: var(--accent-light, rgba(99,102,241,0.06));
}

/* Message */
.ai-message {
  display: flex;
  gap: 8px;
}

.ai-message.user { flex-direction: row-reverse; }

.ai-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  flex-shrink: 0;
}

.ai-message.user .ai-avatar {
  background: var(--accent, #6366f1);
  color: #fff;
}

.ai-message.assistant .ai-avatar {
  background: var(--accent-light, rgba(99,102,241,0.1));
  color: var(--accent, #6366f1);
}

.ai-bubble {
  max-width: 80%;
  padding: 8px 12px;
  border-radius: 12px;
  font-size: 12px;
  line-height: 1.5;
}

.ai-message.user .ai-bubble {
  background: var(--accent, #6366f1);
  color: #fff;
  border-radius: 12px 12px 4px 12px;
}

.ai-message.assistant .ai-bubble {
  background: var(--bg-secondary, #f8fafc);
  color: var(--text-primary, #0f172a);
  border-radius: 12px 12px 12px 4px;
  border: 1px solid var(--border, #e2e8f0);
}

.ai-bubble-content :deep(code) {
  background: rgba(0,0,0,0.08);
  padding: 1px 4px;
  border-radius: 3px;
  font-size: 11px;
  font-family: monospace;
}

.ai-bubble-content :deep(strong) {
  font-weight: 700;
}

/* Typing */
.ai-bubble.typing {
  display: flex;
  gap: 4px;
  padding: 12px 16px;
  align-items: center;
}

.typing-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--text-muted, #94a3b8);
  animation: typingBounce 1.4s ease-in-out infinite;
}

.typing-dot:nth-child(2) { animation-delay: 0.2s; }
.typing-dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes typingBounce {
  0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
  30% { transform: translateY(-6px); opacity: 1; }
}

/* Bubble Actions */
.ai-bubble-actions {
  display: flex;
  gap: 4px;
  margin-top: 8px;
  flex-wrap: wrap;
}

.ai-action-btn {
  padding: 4px 10px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 6px;
  background: var(--bg-panel, #ffffff);
  cursor: pointer;
  font-size: 10px;
  font-weight: 600;
  color: var(--accent, #6366f1);
  display: flex;
  align-items: center;
  gap: 4px;
  transition: all 0.15s;
  font-family: inherit;
}

.ai-action-btn:hover {
  background: var(--accent, #6366f1);
  color: #fff;
  border-color: var(--accent, #6366f1);
}

/* Input Area */
.ai-input-area {
  border-top: 1px solid var(--border, #e2e8f0);
  padding: 10px;
}

.ai-mode-tabs {
  display: flex;
  gap: 2px;
  margin-bottom: 8px;
}

.ai-mode-tab {
  flex: 1;
  padding: 5px 6px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 6px;
  background: var(--bg-secondary, #f8fafc);
  cursor: pointer;
  font-size: 9px;
  font-weight: 600;
  color: var(--text-muted, #94a3b8);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 3px;
  transition: all 0.15s;
  font-family: inherit;
}

.ai-mode-tab:hover {
  color: var(--text-secondary, #475569);
}

.ai-mode-tab.active {
  background: var(--accent-light, rgba(99,102,241,0.1));
  color: var(--accent, #6366f1);
  border-color: var(--accent, #6366f1);
}

.ai-input-row {
  display: flex;
  gap: 6px;
}

.ai-textarea {
  flex: 1;
  padding: 8px 10px;
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 10px;
  background: var(--bg-secondary, #f8fafc);
  color: var(--text-primary, #0f172a);
  font-size: 12px;
  outline: none;
  resize: none;
  line-height: 1.4;
  font-family: inherit;
  transition: border-color 0.15s;
}

.ai-textarea:focus {
  border-color: var(--accent, #6366f1);
  box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}

.ai-textarea::placeholder { color: var(--text-muted, #94a3b8); }

.ai-send-btn {
  width: 36px;
  height: 36px;
  border: none;
  background: var(--accent, #6366f1);
  color: #fff;
  border-radius: 10px;
  cursor: pointer;
  font-size: 13px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  align-self: flex-end;
  transition: all 0.15s;
}

.ai-send-btn:hover:not(:disabled) {
  background: var(--accent-hover, #4f46e5);
  transform: scale(1.05);
}

.ai-send-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.ai-input-hint {
  font-size: 9px;
  color: var(--text-muted, #94a3b8);
  margin-top: 4px;
  text-align: center;
}

.ai-input-hint kbd {
  font-size: 9px;
  padding: 1px 4px;
  border-radius: 3px;
  background: var(--bg-secondary, #f8fafc);
  border: 1px solid var(--border, #e2e8f0);
}

/* Transition */
.ai-slide-enter-active { animation: aiSlideIn 0.3s ease; }
.ai-slide-leave-active { animation: aiSlideIn 0.2s ease reverse; }

@keyframes aiSlideIn {
  from { opacity: 0; transform: translateY(16px) scale(0.95); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

/* Responsive */
@media (max-width: 480px) {
  .ai-panel {
    right: 8px;
    left: 8px;
    width: auto;
    bottom: 10px;
    max-height: 60vh;
  }
}
</style>