<template>
    <Teleport to="body">
        <Transition name="ai-emerge">
            <div
                v-if="internalVisible"
                ref="panelRef"
                class="ai-panel"
                :class="[
                    isDark ? 'ai-panel--dark' : 'ai-panel--light',
                    isMaximized ? 'ai-panel--max' : '',
                    isMinimized ? 'ai-panel--min' : '',
                ]"
                :style="panelStyle"
            >
                <!-- ══ DRAG HANDLE / HEADER ══════════════════════════ -->
                <div
                    class="ai-header"
                    @mousedown="startDrag"
                    @touchstart.prevent="startDragTouch"
                >
                    <!-- Live pulse orb -->
                    <div class="ai-orb-wrap">
                        <div class="ai-orb">
                            <div class="ai-orb-ring ai-orb-ring--1" />
                            <div class="ai-orb-ring ai-orb-ring--2" />
                            <svg
                                class="ai-orb-icon"
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"
                                    fill="currentColor"
                                    opacity=".2"
                                />
                                <path
                                    d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1 1 .196 2.793-1.1 2.793H3.897c-1.296 0-2.1-1.793-1.1-2.793L4.1 15.3"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>
                    </div>

                    <div class="ai-title-group">
                        <span class="ai-title-text">AI Assistant</span>
                        <span class="ai-status-pill">
                            <span
                                class="ai-status-dot"
                                :class="{ 'ai-status-dot--loading': isLoading }"
                            />
                            {{ isLoading ? "Thinking…" : "Ready" }}
                        </span>
                    </div>

                    <div class="ai-header-controls">
                        <!-- History button -->
                        <button
                            class="ai-ctrl-btn"
                            @click.stop="showHistory = !showHistory"
                            title="History"
                        >
                            <svg
                                viewBox="0 0 16 16"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5"
                            >
                                <circle cx="8" cy="8" r="7" />
                                <path d="M8 4v4l3 2" stroke-linecap="round" />
                            </svg>
                        </button>
                        <!-- Token count -->
                        <div
                            class="ai-token-count"
                            :title="`~${tokenEstimate} tokens used`"
                        >
                            <svg
                                viewBox="0 0 16 16"
                                fill="currentColor"
                                class="w-3 h-3"
                            >
                                <circle
                                    cx="8"
                                    cy="8"
                                    r="7"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    fill="none"
                                />
                                <path
                                    d="M8 4v4l3 2"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    fill="none"
                                />
                            </svg>
                            {{ tokenEstimate }}
                        </div>
                        <button
                            class="ai-ctrl-btn"
                            @click.stop="toggleMinimize"
                            :title="isMinimized ? 'Expand' : 'Minimize'"
                        >
                            <svg
                                viewBox="0 0 16 16"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <line x1="3" y1="8" x2="13" y2="8" />
                            </svg>
                        </button>
                        <button
                            class="ai-ctrl-btn"
                            @click.stop="toggleMaximize"
                            :title="isMaximized ? 'Restore' : 'Maximize'"
                        >
                            <svg
                                viewBox="0 0 16 16"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <rect
                                    v-if="isMaximized"
                                    x="4"
                                    y="4"
                                    width="8"
                                    height="8"
                                    rx="1"
                                />
                                <path
                                    v-else
                                    d="M3 5V3h2M11 3h2v2M13 11v2h-2M5 13H3v-2"
                                />
                            </svg>
                        </button>
                        <button
                            class="ai-ctrl-btn ai-ctrl-btn--close"
                            @click.stop="handleClose"
                            title="Close"
                        >
                            <svg
                                viewBox="0 0 16 16"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M4 4l8 8M12 4l-8 8" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Collapsible body -->
                <Transition name="ai-collapse">
                    <div v-if="!isMinimized" class="ai-body">
                        <!-- ══ TOOLBAR ═══════════════════════════════════ -->
                        <div class="ai-toolbar">
                            <button
                                v-for="mode in modes"
                                :key="mode.value"
                                class="ai-mode-btn"
                                :class="{
                                    'ai-mode-btn--active':
                                        selectedMode === mode.value,
                                }"
                                @click="selectedMode = mode.value"
                            >
                                <span class="ai-mode-icon" v-html="mode.svg" />
                                <span>{{ mode.label }}</span>
                            </button>

                            <div class="ai-toolbar-sep" />

                            <!-- Tone selector -->
                            <div class="ai-source-select-wrap">
                                <select
                                    v-model="selectedTone"
                                    class="ai-source-select"
                                >
                                    <option value="professional">
                                        Professional
                                    </option>
                                    <option value="casual">Casual</option>
                                    <option value="persuasive">
                                        Persuasive
                                    </option>
                                    <option value="technical">Technical</option>
                                </select>
                                <svg
                                    class="ai-source-arrow"
                                    viewBox="0 0 10 6"
                                    fill="none"
                                >
                                    <path
                                        d="M1 1l4 4 4-4"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                    />
                                </svg>
                            </div>

                            <!-- Word count target -->
                            <div class="ai-source-select-wrap">
                                <select
                                    v-model="wordTarget"
                                    class="ai-source-select"
                                >
                                    <option value="short">Short</option>
                                    <option value="medium">Medium</option>
                                    <option value="long">Detailed</option>
                                </select>
                                <svg
                                    class="ai-source-arrow"
                                    viewBox="0 0 10 6"
                                    fill="none"
                                >
                                    <path
                                        d="M1 1l4 4 4-4"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                    />
                                </svg>
                            </div>

                            <div class="ai-toolbar-sep" />

                            <!-- AI Source -->
                            <div class="ai-source-select-wrap">
                                <select
                                    v-model="aiSource"
                                    class="ai-source-select"
                                >
                                    <option value="local">Local AI</option>
                                    <option value="pollinations">
                                        Pollinations
                                    </option>
                                    <option value="openai_free">
                                        GPT-4o-mini
                                    </option>
                                </select>
                                <svg
                                    class="ai-source-arrow"
                                    viewBox="0 0 10 6"
                                    fill="none"
                                >
                                    <path
                                        d="M1 1l4 4 4-4"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                    />
                                </svg>
                            </div>

                            <button
                                class="ai-toolbar-icon-btn"
                                @click="clearChat"
                                title="Clear chat"
                            >
                                <svg
                                    viewBox="0 0 20 20"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                >
                                    <path
                                        d="M6 6l8 8M14 6l-8 8"
                                        stroke-linecap="round"
                                    />
                                </svg>
                            </button>
                            <button
                                class="ai-toolbar-icon-btn"
                                @click="exportChat"
                                title="Export chat"
                            >
                                <svg
                                    viewBox="0 0 20 20"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                >
                                    <path
                                        d="M4 16h12M10 4v8m0 0l-3-3m3 3l3-3"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </button>
                        </div>

                        <!-- ══ HISTORY PANEL ══════════════════════════════ -->
                        <Transition name="ai-collapse">
                            <div v-if="showHistory" class="ai-history-panel">
                                <div class="ai-history-header">
                                    Recent Conversations
                                </div>
                                <div
                                    v-if="chatHistory.length"
                                    class="ai-history-list"
                                >
                                    <div
                                        v-for="(chat, ci) in chatHistory"
                                        :key="ci"
                                        class="ai-history-item"
                                        @click="loadHistory(ci)"
                                    >
                                        <span class="ai-history-preview">{{
                                            chat.preview
                                        }}</span>
                                        <span class="ai-history-date">{{
                                            chat.date
                                        }}</span>
                                        <button
                                            class="ai-history-delete"
                                            @click.stop="deleteHistory(ci)"
                                        >
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                </div>
                                <div v-else class="ai-history-empty">
                                    No saved conversations
                                </div>
                            </div>
                        </Transition>

                        <!-- ══ MESSAGES ═══════════════════════════════════ -->
                        <div class="ai-messages" ref="messagesEl">
                            <!-- Welcome screen -->
                            <Transition name="ai-fade">
                                <div
                                    v-if="messages.length === 0 && !showHistory"
                                    class="ai-welcome"
                                >
                                    <div class="ai-welcome-visual">
                                        <div class="ai-welcome-grid">
                                            <div
                                                v-for="n in 9"
                                                :key="n"
                                                class="ai-welcome-cell"
                                                :style="`--i:${n}`"
                                            />
                                        </div>
                                        <div class="ai-welcome-center">
                                            <svg
                                                viewBox="0 0 48 48"
                                                fill="none"
                                                class="ai-welcome-logo"
                                            >
                                                <rect
                                                    width="48"
                                                    height="48"
                                                    rx="14"
                                                    fill="url(#wg)"
                                                />
                                                <defs>
                                                    <linearGradient
                                                        id="wg"
                                                        x1="0"
                                                        y1="0"
                                                        x2="48"
                                                        y2="48"
                                                    >
                                                        <stop
                                                            stop-color="#6366f1"
                                                        />
                                                        <stop
                                                            offset="1"
                                                            stop-color="#8b5cf6"
                                                        />
                                                    </linearGradient>
                                                </defs>
                                                <path
                                                    d="M16 32V20a4 4 0 014-4h8a4 4 0 014 4v12"
                                                    stroke="white"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                />
                                                <path
                                                    d="M12 32h24"
                                                    stroke="white"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                />
                                                <circle
                                                    cx="24"
                                                    cy="20"
                                                    r="2"
                                                    fill="white"
                                                />
                                            </svg>
                                        </div>
                                    </div>
                                    <h3 class="ai-welcome-title">
                                        What can I help you build?
                                    </h3>
                                    <p class="ai-welcome-sub">
                                        Generate content, charts, headlines, or
                                        enhance existing text for your report.
                                    </p>
                                    <div class="ai-chips-grid">
                                        <button
                                            v-for="sug in suggestions"
                                            :key="sug.text"
                                            class="ai-chip"
                                            @click="useSuggestion(sug)"
                                        >
                                            <span class="ai-chip-icon">{{
                                                sug.icon
                                            }}</span>
                                            <span class="ai-chip-text">{{
                                                sug.text
                                            }}</span>
                                        </button>
                                    </div>
                                </div>
                            </Transition>

                            <!-- Message list -->
                            <TransitionGroup
                                name="ai-msg"
                                tag="div"
                                class="ai-msg-list"
                            >
                                <div
                                    v-for="(msg, idx) in messages"
                                    :key="msg.id"
                                    class="ai-msg-wrap"
                                    :class="
                                        msg.role === 'user'
                                            ? 'ai-msg-wrap--user'
                                            : 'ai-msg-wrap--ai'
                                    "
                                >
                                    <div
                                        class="ai-msg-avatar"
                                        :class="
                                            msg.role === 'user'
                                                ? 'ai-msg-avatar--user'
                                                : 'ai-msg-avatar--ai'
                                        "
                                    >
                                        <svg
                                            v-if="msg.role === 'assistant'"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            class="w-3.5 h-3.5"
                                        >
                                            <path
                                                d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1 1 .196 2.793-1.1 2.793H3.897c-1.296 0-2.1-1.793-1.1-2.793L4.1 15.3"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                            />
                                        </svg>
                                        <svg
                                            v-else
                                            viewBox="0 0 24 24"
                                            fill="currentColor"
                                            class="w-3.5 h-3.5"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                    <div class="ai-msg-body">
                                        <div
                                            class="ai-bubble"
                                            :class="
                                                msg.role === 'user'
                                                    ? 'ai-bubble--user'
                                                    : 'ai-bubble--ai'
                                            "
                                        >
                                            <div
                                                class="ai-bubble-text"
                                                v-html="
                                                    renderMarkdown(msg.content)
                                                "
                                            />
                                            <div
                                                v-if="hasCode(msg.content)"
                                                class="ai-code-toolbar"
                                            >
                                                <button
                                                    class="ai-code-btn"
                                                    @click="
                                                        copyText(
                                                            stripMarkdown(
                                                                msg.content,
                                                            ),
                                                        )
                                                    "
                                                >
                                                    <svg
                                                        viewBox="0 0 16 16"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.5"
                                                        class="w-3 h-3"
                                                    >
                                                        <path
                                                            d="M5 4H3a1 1 0 00-1 1v8a1 1 0 001 1h8a1 1 0 001-1v-2M8 1h5v5M8 8l5-5"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                        />
                                                    </svg>
                                                    Copy
                                                </button>
                                            </div>
                                        </div>
                                        <!-- Feedback thumbs -->
                                        <div
                                            v-if="msg.role === 'assistant'"
                                            class="ai-feedback-row"
                                        >
                                            <button
                                                class="ai-feedback-btn"
                                                :class="{
                                                    active:
                                                        msg.feedback === 'up',
                                                }"
                                                @click="rateMessage(msg, 'up')"
                                                title="Helpful"
                                            >
                                                👍
                                            </button>
                                            <button
                                                class="ai-feedback-btn"
                                                :class="{
                                                    active:
                                                        msg.feedback === 'down',
                                                }"
                                                @click="
                                                    rateMessage(msg, 'down')
                                                "
                                                title="Not helpful"
                                            >
                                                👎
                                            </button>
                                            <button
                                                class="ai-feedback-btn"
                                                @click="regenerateMessage(idx)"
                                                title="Regenerate"
                                            >
                                                🔄
                                            </button>
                                        </div>
                                        <!-- Actions -->
                                        <div
                                            v-if="
                                                msg.role === 'assistant' &&
                                                msg.actions?.length
                                            "
                                            class="ai-actions-bar"
                                        >
                                            <button
                                                v-for="action in msg.actions"
                                                :key="action.label"
                                                class="ai-action-pill"
                                                :class="
                                                    action.primary
                                                        ? 'ai-action-pill--primary'
                                                        : ''
                                                "
                                                @click="handleAction(action)"
                                                :title="action.label"
                                            >
                                                <span
                                                    v-html="action.svg"
                                                    class="ai-action-icon-svg"
                                                />
                                                {{ action.label }}
                                            </button>
                                        </div>
                                        <div class="ai-msg-time">
                                            {{ msg.time }}
                                        </div>
                                    </div>
                                </div>
                            </TransitionGroup>

                            <!-- Typing indicator -->
                            <div
                                v-if="isLoading"
                                class="ai-msg-wrap ai-msg-wrap--ai"
                            >
                                <div class="ai-msg-avatar ai-msg-avatar--ai">
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        class="w-3.5 h-3.5"
                                    >
                                        <path
                                            d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1 1 .196 2.793-1.1 2.793H3.897c-1.296 0-2.1-1.793-1.1-2.793L4.1 15.3"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                        />
                                    </svg>
                                </div>
                                <div class="ai-msg-body">
                                    <div
                                        class="ai-bubble ai-bubble--ai ai-bubble--typing"
                                    >
                                        <div class="ai-typing-dots">
                                            <span /><span /><span />
                                        </div>
                                        <span class="ai-typing-label">{{
                                            thinkingLabel
                                        }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ══ INPUT AREA ══════════════════════════════════ -->
                        <div class="ai-input-zone">
                            <div
                                class="ai-quick-row"
                                v-if="quickPrompts[selectedMode]"
                            >
                                <button
                                    v-for="q in quickPrompts[selectedMode]"
                                    :key="q"
                                    class="ai-quick-pill"
                                    @click="injectPrompt(q)"
                                >
                                    {{ q }}
                                </button>
                            </div>

                            <div
                                class="ai-input-box"
                                :class="{ 'ai-input-box--focus': inputFocused }"
                            >
                                <div
                                    class="ai-mode-strip"
                                    :class="`ai-mode-strip--${selectedMode}`"
                                />
                                <textarea
                                    ref="textareaEl"
                                    v-model="prompt"
                                    class="ai-textarea"
                                    :placeholder="currentPlaceholder"
                                    rows="3"
                                    @keydown.enter.exact.prevent="sendMessage"
                                    @keydown.enter.shift.exact="prompt += '\n'"
                                    @focus="inputFocused = true"
                                    @blur="inputFocused = false"
                                    @input="adjustTextarea"
                                />
                                <div class="ai-input-footer">
                                    <div
                                        class="ai-char-count"
                                        :class="{
                                            'ai-char-count--warn':
                                                prompt.length > 800,
                                        }"
                                    >
                                        {{ prompt.length }}/1000
                                    </div>
                                    <div class="ai-input-actions">
                                        <!-- Voice input -->
                                        <button
                                            class="ai-attach-btn"
                                            @click="startVoiceInput"
                                            :class="{ recording: isRecording }"
                                            title="Voice input"
                                        >
                                            <i
                                                :class="
                                                    isRecording
                                                        ? 'fa-solid fa-microphone'
                                                        : 'fa-solid fa-microphone-lines'
                                                "
                                            ></i>
                                        </button>
                                        <!-- Context attach -->
                                        <button
                                            class="ai-attach-btn"
                                            @click="attachContext"
                                            title="Attach canvas context"
                                        >
                                            <svg
                                                viewBox="0 0 20 20"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                class="w-4 h-4"
                                            >
                                                <path
                                                    d="M16.5 10.5V6.75a4.5 4.5 0 00-9 0v8.25a3 3 0 006 0V9A1.5 1.5 0 1010 9v7.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                            </svg>
                                        </button>
                                        <button
                                            class="ai-send-btn"
                                            :class="{
                                                'ai-send-btn--ready':
                                                    prompt.trim() && !isLoading,
                                            }"
                                            :disabled="
                                                !prompt.trim() || isLoading
                                            "
                                            @click="sendMessage"
                                            title="Send (Enter)"
                                        >
                                            <svg
                                                v-if="!isLoading"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                                class="w-4 h-4"
                                            >
                                                <path
                                                    d="M3.105 2.289a.75.75 0 00-.826.95l1.414 4.925A1.5 1.5 0 005.135 9.25h6.115a.75.75 0 010 1.5H5.135a1.5 1.5 0 00-1.442 1.086l-1.414 4.926a.75.75 0 00.826.95 28.896 28.896 0 0015.293-7.154.75.75 0 000-1.115A28.897 28.897 0 003.105 2.289z"
                                                />
                                            </svg>
                                            <svg
                                                v-else
                                                viewBox="0 0 20 20"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                class="w-4 h-4 ai-spin"
                                            >
                                                <path
                                                    d="M10 2a8 8 0 100 16A8 8 0 0010 2z"
                                                    opacity=".2"
                                                />
                                                <path
                                                    d="M10 2a8 8 0 018 8"
                                                    stroke-linecap="round"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="ai-hint-bar">
                                <span><kbd>↵</kbd> Send</span>
                                <span><kbd>⇧↵</kbd> New line</span>
                                <span class="ai-hint-sep">·</span>
                                <span class="ai-model-tag">{{
                                    modelLabel
                                }}</span>
                            </div>
                        </div>
                    </div>
                </Transition>

                <!-- Resize handle -->
                <div
                    v-if="!isMinimized && !isMaximized"
                    class="ai-resize-handle ai-resize-se"
                    @mousedown.stop="startResize"
                    @touchstart.prevent.stop="startResizeTouch"
                />
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import {
    ref,
    reactive,
    computed,
    watch,
    nextTick,
    onMounted,
    onBeforeUnmount,
} from "vue";

const props = defineProps({
    visible: { type: Boolean, default: true },
    report: { type: Object, required: true },
    isDark: { type: Boolean, default: false },
    selectedElement: { type: Object, default: null },
});

const internalVisible = ref(true);
watch(
    () => props.visible,
    (newVal) => {
        if (newVal) internalVisible.value = true;
    },
);

const handleClose = () => {
    internalVisible.value = false;
    setTimeout(() => emit("close"), 100);
};
const emit = defineEmits(["close", "insert-content", "insert-chart"]);

// ═══ REFS ═══════════════════════════════════════════════════
const panelRef = ref(null);
const messagesEl = ref(null);
const textareaEl = ref(null);

// ═══ PANEL POSITION ════════════════════════════════════════
const pos = reactive({ x: 0, y: 0 });
const size = reactive({ w: 420, h: 580 });
const isMaximized = ref(false);
const isMinimized = ref(false);
const inputFocused = ref(false);
const showHistory = ref(false);

// Dragging
let dragging = false,
    dragOff = { x: 0, y: 0 };
let resizing = false,
    resizeOff = { x: 0, y: 0, w: 0, h: 0 };

const panelStyle = computed(() => {
    if (isMaximized.value)
        return { inset: "20px", width: "auto", height: "auto" };
    return {
        transform: `translate(${pos.x}px, ${pos.y}px)`,
        width: size.w + "px",
        height: isMinimized.value ? "auto" : size.h + "px",
    };
});

onMounted(() => {
    pos.x = window.innerWidth - size.w - 24;
    pos.y = window.innerHeight - size.h - 24;
    clampPos();
    nextTick(() => textareaEl.value?.focus());
    loadChatHistory();
});

const clampPos = () => {
    pos.x = Math.max(0, Math.min(pos.x, window.innerWidth - size.w));
    pos.y = Math.max(0, Math.min(pos.y, window.innerHeight - 60));
};

// ═══ DRAG / RESIZE ═════════════════════════════════════════
const startDrag = (e) => {
    if (isMaximized.value || e.target.closest(".ai-ctrl-btn")) return;
    dragging = true;
    dragOff = { x: e.clientX - pos.x, y: e.clientY - pos.y };
    document.addEventListener("mousemove", onDrag);
    document.addEventListener("mouseup", stopDrag);
};
const onDrag = (e) => {
    if (!dragging) return;
    pos.x = e.clientX - dragOff.x;
    pos.y = e.clientY - dragOff.y;
    clampPos();
};
const stopDrag = () => {
    dragging = false;
    document.removeEventListener("mousemove", onDrag);
    document.removeEventListener("mouseup", stopDrag);
};
const startDragTouch = (e) => {
    if (isMaximized.value) return;
    const t = e.touches[0];
    dragging = true;
    dragOff = { x: t.clientX - pos.x, y: t.clientY - pos.y };
    document.addEventListener("touchmove", onDragTouch, { passive: false });
    document.addEventListener("touchend", stopDragTouch);
};
const onDragTouch = (e) => {
    e.preventDefault();
    if (!dragging) return;
    const t = e.touches[0];
    pos.x = t.clientX - dragOff.x;
    pos.y = t.clientY - dragOff.y;
    clampPos();
};
const stopDragTouch = () => {
    dragging = false;
    document.removeEventListener("touchmove", onDragTouch);
    document.removeEventListener("touchend", stopDragTouch);
};
const startResize = (e) => {
    resizing = true;
    resizeOff = { x: e.clientX, y: e.clientY, w: size.w, h: size.h };
    document.addEventListener("mousemove", onResize);
    document.addEventListener("mouseup", stopResize);
};
const onResize = (e) => {
    if (!resizing) return;
    size.w = Math.max(300, resizeOff.w + (e.clientX - resizeOff.x));
    size.h = Math.max(380, resizeOff.h + (e.clientY - resizeOff.y));
};
const stopResize = () => {
    resizing = false;
    document.removeEventListener("mousemove", onResize);
    document.removeEventListener("mouseup", stopResize);
};
const toggleMaximize = () => {
    isMaximized.value = !isMaximized.value;
    if (isMinimized.value) isMinimized.value = false;
};
const toggleMinimize = () => {
    isMinimized.value = !isMinimized.value;
    if (isMaximized.value) isMaximized.value = false;
};

// ═══ CHAT STATE ════════════════════════════════════════════
const prompt = ref("");
const messages = ref([]);
const isLoading = ref(false);
const selectedMode = ref("generate");
const selectedTone = ref("professional");
const wordTarget = ref("medium");
const aiSource = ref("local");
const isRecording = ref(false);
const chatHistory = ref([]);
let msgIdCounter = 0;
let thinkingTimer = null;

const thinkingLabels = [
    "Thinking…",
    "Generating…",
    "Crafting…",
    "Analyzing…",
    "Writing…",
];
const thinkingLabel = ref(thinkingLabels[0]);
const tokenEstimate = computed(() =>
    Math.ceil(
        (messages.value.reduce((s, m) => s + m.content.length, 0) +
            prompt.value.length) /
            4,
    ),
);
const modelLabel = computed(
    () =>
        ({
            local: "Local API",
            pollinations: "Pollinations AI",
            openai_free: "GPT-4o-mini",
        })[aiSource.value],
);

// ═══ MODES ═════════════════════════════════════════════════
const modes = [
    {
        label: "Generate",
        value: "generate",
        svg: `<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" class="w-3.5 h-3.5"><path d="M8 1l1.5 4.5L14 7l-4.5 1.5L8 13l-1.5-4.5L2 7l4.5-1.5L8 1z" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
    },
    {
        label: "Enhance",
        value: "enhance",
        svg: `<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" class="w-3.5 h-3.5"><path d="M2 13l4-4 3 3 5-7" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
    },
    {
        label: "Summarize",
        value: "summarize",
        svg: `<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" class="w-3.5 h-3.5"><path d="M2 4h12M2 8h8M2 12h5" stroke-linecap="round"/></svg>`,
    },
    {
        label: "Chart",
        value: "chart",
        svg: `<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" class="w-3.5 h-3.5"><path d="M2 13V7l3-3 3 3 3-5 3 2" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
    },
    {
        label: "Translate",
        value: "translate",
        svg: `<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" class="w-3.5 h-3.5"><circle cx="5" cy="8" r="3"/><circle cx="11" cy="8" r="3"/><path d="M8 5v6" stroke-linecap="round"/></svg>`,
    },
    {
        label: "Image",
        value: "image",
        svg: `<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" class="w-3.5 h-3.5"><rect x="2" y="3" width="12" height="10" rx="2"/><circle cx="6" cy="7" r="1.5"/><path d="M14 10l-3-3-2 2-2-2-3 3" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
    },
];

const placeholders = {
    generate: "✦ Describe the content you want to create…",
    enhance: "✦ Paste text to improve professionally…",
    summarize: "✦ Paste text to summarize…",
    chart: "✦ Describe the chart data you need…",
    translate: "✦ Paste text and specify target language…",
    image: "✦ Describe the image you want to generate…",
};
const currentPlaceholder = computed(
    () => placeholders[selectedMode.value] || "Ask anything…",
);

const quickPrompts = {
    generate: [
        "Executive summary",
        "Key findings",
        "Conclusion",
        "Introduction",
    ],
    enhance: [
        "Make professional",
        "Fix grammar",
        "More persuasive",
        "Simplify",
    ],
    summarize: ["Bullet points", "3 sentences", "Key metrics", "Brief"],
    chart: ["Q4 revenue", "User growth 6mo", "Market share", "Sales vs target"],
    translate: ["To Spanish", "To French", "To Arabic", "To German"],
    image: [
        "Business chart",
        "Team photo",
        "Abstract background",
        "Report cover",
    ],
};

const suggestions = [
    {
        icon: "📊",
        text: "Q4 business report executive summary",
        mode: "generate",
    },
    { icon: "📈", text: "Revenue chart data for last 6 months", mode: "chart" },
    {
        icon: "✍️",
        text: "Professional headline about market growth",
        mode: "generate",
    },
    {
        icon: "🔍",
        text: "Summarize KPIs into 3 bullet points",
        mode: "summarize",
    },
    {
        icon: "⚡",
        text: "Enhance this paragraph professionally",
        mode: "enhance",
    },
    {
        icon: "🌐",
        text: "Translate report section to Spanish",
        mode: "translate",
    },
];

const useSuggestion = (sug) => {
    selectedMode.value = sug.mode;
    prompt.value = sug.text;
    nextTick(() => textareaEl.value?.focus());
};
const injectPrompt = (text) => {
    prompt.value = text;
    nextTick(() => textareaEl.value?.focus());
};

// ═══ MARKDOWN ═════════════════════════════════════════════
const renderMarkdown = (text) =>
    text
        .replace(
            /```(\w*)\n?([\s\S]*?)```/g,
            (_, lang, code) =>
                `<pre class="ai-code-block"><code>${escHtml(code.trim())}</code></pre>`,
        )
        .replace(/`([^`]+)`/g, '<code class="ai-inline-code">$1</code>')
        .replace(/\*\*([^*]+)\*\*/g, "<strong>$1</strong>")
        .replace(/\*([^*]+)\*/g, "<em>$1</em>")
        .replace(/^#{1,3}\s+(.+)/gm, '<p class="ai-md-heading">$1</p>')
        .replace(/^[-•]\s+(.+)/gm, "<li>$1</li>")
        .replace(/(<li>[\s\S]+<\/li>)/g, '<ul class="ai-md-list">$1</ul>')
        .replace(/\n{2,}/g, '</p><p class="ai-md-p">')
        .replace(/\n/g, "<br>");

const escHtml = (s) =>
    s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
const hasCode = (t) => t.includes("```") || t.includes("`");
const stripMarkdown = (t) =>
    t.replace(/```[\s\S]*?```/g, (m) => m.slice(3, -3)).replace(/[`*#_~]/g, "");
const fmtTime = () =>
    new Date().toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });

// ═══ SEND MESSAGE ═════════════════════════════════════════
const sendMessage = async () => {
    const text = prompt.value.trim();
    if (!text || isLoading.value) return;
    messages.value.push({
        id: ++msgIdCounter,
        role: "user",
        content: text,
        time: fmtTime(),
    });
    prompt.value = "";
    isLoading.value = true;
    let li = 0;
    thinkingTimer = setInterval(() => {
        li = (li + 1) % thinkingLabels.length;
        thinkingLabel.value = thinkingLabels[li];
    }, 1200);
    scrollBottom();

    try {
        let responseText = "";
        let chartData = null;
        const apiType =
            {
                generate: "text",
                enhance: "text",
                summarize: "summary",
                chart: "chart_data",
                translate: "text",
                image: "image",
            }[selectedMode.value] || "text";
        const toneInstr = {
            professional: "Use formal, business-appropriate language.",
            casual: "Use friendly, conversational language.",
            persuasive: "Use compelling, convincing language.",
            technical: "Use precise, data-driven language.",
        };
        const wordInstr = {
            short: "Keep it concise - 2-3 sentences max.",
            medium: "Provide a moderate-length response.",
            long: "Provide a comprehensive, detailed response.",
        };

        if (aiSource.value === "pollinations") {
            const sysPrompt = `${buildSystemPrompt()}\nTone: ${toneInstr[selectedTone.value]}\nLength: ${wordInstr[wordTarget.value]}`;
            const resp = await fetch(
                `https://text.pollinations.ai/${encodeURIComponent(`${sysPrompt}\n\nUser: ${text}`)}`,
                {
                    method: "GET",
                    headers: { Accept: "text/plain" },
                    signal: AbortSignal.timeout(30000),
                },
            );
            if (resp.ok) responseText = await resp.text();
            else throw new Error("API failed");
        } else {
            const resp = await fetch("/api/ai/generate", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": getCsrf(),
                    Accept: "application/json",
                },
                body: JSON.stringify({
                    prompt: text,
                    type: apiType,
                    tone: selectedTone.value,
                    length: wordTarget.value,
                }),
                signal: AbortSignal.timeout(20000),
            });
            const d = await resp.json();
            if (apiType === "chart_data") {
                chartData = d;
                responseText = buildChartPreview(d);
            } else
                responseText =
                    d.result || d.enhanced || d.summary || "No response.";
        }

        clearInterval(thinkingTimer);
        const actions = [];
        if (chartData)
            actions.push({
                label: "Insert Chart",
                svg: chartSvg,
                action: "insert-chart",
                data: chartData,
                primary: true,
            });
        else {
            actions.push({
                label: "Insert",
                svg: insertSvg,
                action: "insert-text",
                data: responseText,
                primary: true,
            });
            actions.push({
                label: "Copy",
                svg: copySvg,
                action: "copy",
                data: responseText,
            });
        }

        messages.value.push({
            id: ++msgIdCounter,
            role: "assistant",
            content: responseText,
            actions,
            time: fmtTime(),
            feedback: null,
        });
        saveChatHistory();
    } catch (err) {
        clearInterval(thinkingTimer);
        messages.value.push({
            id: ++msgIdCounter,
            role: "assistant",
            content:
                err.name === "TimeoutError"
                    ? "⏱️ Timed out. Try a shorter prompt."
                    : "❌ Connection failed.",
            actions: [],
            time: fmtTime(),
            feedback: null,
        });
    }
    isLoading.value = false;
    scrollBottom();
};

const buildSystemPrompt = () =>
    ({
        generate: "Professional report writer.",
        enhance: "Professional editor.",
        summarize: "Expert summarizer.",
        chart: "Data analyst.",
        translate: "Professional translator.",
        image: "Image prompt generator.",
    })[selectedMode.value] || "Helpful AI assistant.";
const buildChartPreview = (d) =>
    `**Chart Generated!**\n\n**Title:** ${d.title || "Chart"}\n**Type:** ${d.suggested_chart_type || "bar-chart"}\n**Labels:** ${(d.labels || []).join(", ")}\n**Values:** ${(d.values || []).join(", ")}\n\n${d.insights || ""}`;

const insertSvg = `<svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 1v8M4 6l3 3 3-3M2 11h10" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
const copySvg = `<svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="4" y="4" width="8" height="8" rx="1"/><path d="M2 10V2h8" stroke-linecap="round"/></svg>`;
const chartSvg = `<svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 11V7l3-3 3 3 3-5" stroke-linecap="round" stroke-linejoin="round"/></svg>`;

const handleAction = (a) => {
    if (a.action === "insert-text")
        emit("insert-content", { type: "text", content: a.data });
    else if (a.action === "insert-chart") emit("insert-chart", a.data);
    else if (a.action === "copy") {
        navigator.clipboard.writeText(stripMarkdown(a.data));
        window.showToast?.("Copied!", "success");
    }
};

const copyText = (t) => {
    navigator.clipboard.writeText(stripMarkdown(t));
    window.showToast?.("Copied!", "success");
};

// ═══ FEEDBACK & REGENERATE ═══════════════════════════════
const rateMessage = (msg, rating) => {
    msg.feedback = rating;
};
const regenerateMessage = (idx) => {
    const userMsg = messages.value
        .slice(0, idx)
        .reverse()
        .find((m) => m.role === "user");
    if (userMsg) {
        prompt.value = userMsg.content;
        sendMessage();
    }
};

// ═══ VOICE INPUT ═════════════════════════════════════════
const startVoiceInput = () => {
    if (
        !("webkitSpeechRecognition" in window) &&
        !("SpeechRecognition" in window)
    ) {
        window.showToast?.(
            "Voice input not supported in this browser",
            "warning",
        );
        return;
    }
    const SpeechRecognition =
        window.SpeechRecognition || window.webkitSpeechRecognition;
    const recognition = new SpeechRecognition();
    recognition.lang = "en-US";
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;
    isRecording.value = true;
    recognition.start();
    recognition.onresult = (e) => {
        prompt.value += e.results[0][0].transcript;
        isRecording.value = false;
    };
    recognition.onerror = () => {
        isRecording.value = false;
    };
    recognition.onend = () => {
        isRecording.value = false;
    };
};

// ═══ CONTEXT ═════════════════════════════════════════════
const attachContext = () => {
    let ctx = props.report?.title ? `[Report: "${props.report.title}"] ` : "";
    if (props.selectedElement?.content) {
        const text = props.selectedElement.content
            .replace(/<[^>]*>/g, "")
            .trim()
            .substring(0, 500);
        if (text) ctx += `[Selected element text: "${text}"] `;
    }
    prompt.value = ctx + prompt.value;
    textareaEl.value?.focus();
};

// ═══ HISTORY ═════════════════════════════════════════════
const saveChatHistory = () => {
    if (messages.value.length < 2) return;
    const preview =
        messages.value[0]?.content?.substring(0, 50) || "Conversation";
    const existing = chatHistory.value.findIndex((h) => h.preview === preview);
    const entry = {
        preview,
        date: new Date().toLocaleDateString(),
        messages: JSON.parse(JSON.stringify(messages.value)),
    };
    if (existing >= 0) chatHistory.value[existing] = entry;
    else chatHistory.value.unshift(entry);
    if (chatHistory.value.length > 20) chatHistory.value.pop();
    localStorage.setItem("ai_chat_history", JSON.stringify(chatHistory.value));
};
const loadChatHistory = () => {
    try {
        const saved = localStorage.getItem("ai_chat_history");
        if (saved) chatHistory.value = JSON.parse(saved);
    } catch (e) {}
};
const loadHistory = (idx) => {
    messages.value = JSON.parse(
        JSON.stringify(chatHistory.value[idx].messages),
    );
    showHistory.value = false;
    scrollBottom();
};
const deleteHistory = (idx) => {
    chatHistory.value.splice(idx, 1);
    localStorage.setItem("ai_chat_history", JSON.stringify(chatHistory.value));
};

// ═══ HELPERS ═════════════════════════════════════════════
const clearChat = () => {
    messages.value = [];
};
const exportChat = () => {
    const text = messages.value
        .map(
            (m) =>
                `[${m.role.toUpperCase()}] ${m.time}\n${stripMarkdown(m.content)}`,
        )
        .join("\n\n---\n\n");
    const blob = new Blob([text], { type: "text/plain" });
    const a = document.createElement("a");
    a.href = URL.createObjectURL(blob);
    a.download = "ai-chat.txt";
    a.click();
};
const adjustTextarea = () => {
    const el = textareaEl.value;
    if (!el) return;
    el.style.height = "auto";
    el.style.height = Math.min(el.scrollHeight, 140) + "px";
};
const scrollBottom = () =>
    nextTick(() => {
        if (messagesEl.value)
            messagesEl.value.scrollTop = messagesEl.value.scrollHeight;
    });
const getCsrf = () =>
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content") || "";

onBeforeUnmount(() => {
    clearInterval(thinkingTimer);
    stopDrag();
    stopResize();
});
</script>

<style scoped>
/* ═══ ALL EXISTING STYLES PRESERVED ═══ */
.ai-panel {
    --ai-bg: #ffffff;
    --ai-bg2: #f8fafc;
    --ai-bg3: #f1f5f9;
    --ai-border: #e2e8f0;
    --ai-border2: #cbd5e1;
    --ai-text: #0f172a;
    --ai-text2: #475569;
    --ai-text3: #94a3b8;
    --ai-accent: #6366f1;
    --ai-accent2: #8b5cf6;
    --ai-accent-l: rgba(99, 102, 241, 0.08);
    --ai-accent-l2: rgba(99, 102, 241, 0.15);
    --ai-user-bg: #6366f1;
    --ai-user-text: #ffffff;
    --ai-shadow:
        0 24px 80px rgba(0, 0, 0, 0.14), 0 4px 20px rgba(0, 0, 0, 0.08);
    --ai-radius: 18px;
    --ai-code-bg: #1e293b;
    --ai-code-text: #e2e8f0;
}
.ai-panel--dark {
    --ai-bg: #18181b;
    --ai-bg2: #27272a;
    --ai-bg3: #3f3f46;
    --ai-border: #3f3f46;
    --ai-border2: #52525b;
    --ai-text: #f4f4f5;
    --ai-text2: #a1a1aa;
    --ai-text3: #71717a;
    --ai-accent: #818cf8;
    --ai-accent2: #a78bfa;
    --ai-accent-l: rgba(129, 140, 248, 0.1);
    --ai-accent-l2: rgba(129, 140, 248, 0.18);
    --ai-user-bg: #4f46e5;
    --ai-shadow: 0 24px 80px rgba(0, 0, 0, 0.5), 0 4px 20px rgba(0, 0, 0, 0.3);
    --ai-code-bg: #0f172a;
    --ai-code-text: #7dd3fc;
}
.ai-panel {
    position: fixed;
    bottom: 24px;
    right: 24px;
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
    -webkit-user-select: none;
    min-width: 300px;
    min-height: 60px;
    font-family:
        "DM Sans",
        -apple-system,
        sans-serif;
}
.ai-panel--max {
    border-radius: 12px !important;
}
.ai-panel::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(
        90deg,
        var(--ai-accent),
        var(--ai-accent2),
        var(--ai-accent)
    );
    background-size: 200% 100%;
    animation: shimmerBar 3s linear infinite;
    z-index: 1;
}
@keyframes shimmerBar {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}
.ai-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px 10px 12px;
    border-bottom: 1px solid var(--ai-border);
    cursor: grab;
    flex-shrink: 0;
    background: linear-gradient(135deg, var(--ai-accent-l), transparent 60%);
    position: relative;
}
.ai-header:active {
    cursor: grabbing;
}
.ai-orb-wrap {
    position: relative;
    flex-shrink: 0;
}
.ai-orb {
    width: 32px;
    height: 32px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--ai-accent), var(--ai-accent2));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    position: relative;
    z-index: 1;
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
.ai-orb-ring--2 {
    animation-delay: 1.2s;
}
@keyframes orbPulse {
    0% {
        transform: scale(0.85);
        opacity: 0.6;
    }
    100% {
        transform: scale(1.4);
        opacity: 0;
    }
}
.ai-orb-icon {
    width: 16px;
    height: 16px;
    color: white;
    position: relative;
    z-index: 1;
}
.ai-title-group {
    display: flex;
    flex-direction: column;
    gap: 1px;
    min-width: 0;
    flex: 1;
}
.ai-title-text {
    font-size: 13px;
    font-weight: 700;
    color: var(--ai-text);
    letter-spacing: -0.01em;
    line-height: 1.2;
}
.ai-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 9px;
    font-weight: 600;
    color: var(--ai-text3);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.ai-status-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: #22c55e;
    animation: statusPulse 2s ease-in-out infinite;
}
.ai-status-dot--loading {
    background: var(--ai-accent);
    animation: statusSpin 0.8s linear infinite;
}
@keyframes statusPulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.4;
    }
}
@keyframes statusSpin {
    to {
        transform: rotate(360deg);
    }
}
.ai-header-controls {
    display: flex;
    align-items: center;
    gap: 2px;
    flex-shrink: 0;
}
.ai-token-count {
    display: flex;
    align-items: center;
    gap: 3px;
    font-size: 9px;
    font-weight: 600;
    color: var(--ai-text3);
    padding: 2px 7px;
    background: var(--ai-bg3);
    border-radius: 99px;
    margin-right: 4px;
    border: 1px solid var(--ai-border);
}
.ai-ctrl-btn {
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
    transition: all 0.14s;
    padding: 0;
}
.ai-ctrl-btn:hover {
    background: var(--ai-bg3);
    color: var(--ai-text);
}
.ai-ctrl-btn--close:hover {
    background: #fee2e2;
    color: #ef4444;
}
.ai-body {
    display: flex;
    flex-direction: column;
    flex: 1;
    overflow: hidden;
}
.ai-toolbar {
    display: flex;
    align-items: center;
    gap: 2px;
    padding: 8px 10px 6px;
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
    padding: 4px 9px;
    border: 1px solid transparent;
    border-radius: 8px;
    background: transparent;
    cursor: pointer;
    font-size: 10px;
    font-weight: 600;
    color: var(--ai-text3);
    white-space: nowrap;
    transition: all 0.14s;
    font-family: inherit;
    flex-shrink: 0;
}
.ai-mode-btn:hover {
    background: var(--ai-bg3);
    color: var(--ai-text2);
}
.ai-mode-btn--active {
    background: var(--ai-accent-l2);
    color: var(--ai-accent);
    border-color: var(--ai-accent);
}
.ai-mode-icon {
    display: flex;
    align-items: center;
}
.ai-toolbar-sep {
    flex-shrink: 0;
    margin: 0 4px;
    width: 1px;
    height: 16px;
    background: var(--ai-border);
}
.ai-source-select-wrap {
    position: relative;
    flex-shrink: 0;
}
.ai-source-select {
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
    transition: border 0.14s;
}
.ai-source-select:focus {
    border-color: var(--ai-accent);
}
.ai-source-arrow {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    width: 10px;
    height: 10px;
    color: var(--ai-text3);
    pointer-events: none;
}
.ai-toolbar-icon-btn {
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
    transition: all 0.14s;
    flex-shrink: 0;
    padding: 0;
}
.ai-toolbar-icon-btn:hover {
    background: var(--ai-bg3);
    color: var(--ai-text);
}

/* ═══ NEW: HISTORY PANEL ═══ */
.ai-history-panel {
    max-height: 200px;
    overflow-y: auto;
    border-bottom: 1px solid var(--ai-border);
    padding: 8px;
}
.ai-history-header {
    font-size: 10px;
    font-weight: 700;
    color: var(--ai-text3);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 6px;
}
.ai-history-list {
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.ai-history-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 8px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.1s;
    font-size: 10px;
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
    font-size: 8px;
    color: var(--ai-text3);
    flex-shrink: 0;
}
.ai-history-delete {
    opacity: 0;
    width: 16px;
    height: 16px;
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--ai-text3);
    font-size: 8px;
    transition: all 0.1s;
}
.ai-history-item:hover .ai-history-delete {
    opacity: 1;
}
.ai-history-delete:hover {
    color: #ef4444;
}
.ai-history-empty {
    text-align: center;
    padding: 12px;
    font-size: 10px;
    color: var(--ai-text3);
}

/* ═══ FEEDBACK ROW ═══ */
.ai-feedback-row {
    display: flex;
    gap: 4px;
    margin-top: 4px;
    padding: 0 2px;
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
    transition: all 0.1s;
}
.ai-feedback-btn:hover {
    background: var(--ai-bg3);
}
.ai-feedback-btn.active {
    background: var(--ai-accent-l);
    border-color: var(--ai-accent);
}

/* ═══ RECORDING ═══ */
.ai-attach-btn.recording {
    background: #ef4444 !important;
    color: #fff !important;
    animation: pulseRec 1s ease-in-out infinite;
}
@keyframes pulseRec {
    0%,
    100% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
    }
    50% {
        box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
    }
}

/* ═══ ALL REMAINING STYLES PRESERVED ═══ */
.ai-messages {
    flex: 1;
    overflow-y: auto;
    padding: 12px 12px 4px;
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
.ai-welcome {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 12px 8px 8px;
    gap: 10px;
}
.ai-welcome-visual {
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
.ai-welcome-cell {
    background: var(--ai-accent);
    border-radius: 4px;
    opacity: 0;
    animation: cellFade 0.6s ease forwards;
    animation-delay: calc(var(--i) * 0.05s);
}
@keyframes cellFade {
    from {
        opacity: 0;
        transform: scale(0.5);
    }
    to {
        opacity: 0.08;
        transform: scale(1);
    }
}
.ai-welcome-center {
    position: relative;
    z-index: 1;
}
.ai-welcome-logo {
    width: 48px;
    height: 48px;
    filter: drop-shadow(0 4px 12px rgba(99, 102, 241, 0.4));
}
.ai-welcome-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--ai-text);
    margin: 0;
}
.ai-welcome-sub {
    font-size: 11px;
    color: var(--ai-text3);
    line-height: 1.5;
    margin: 0;
    max-width: 280px;
}
.ai-chips-grid {
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
    transition: all 0.15s;
    font-family: inherit;
}
.ai-chip:hover {
    border-color: var(--ai-accent);
    background: var(--ai-accent-l);
    color: var(--ai-accent);
    transform: translateY(-1px);
}
.ai-chip-icon {
    font-size: 13px;
    flex-shrink: 0;
}
.ai-chip-text {
    line-height: 1.3;
}
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
.ai-msg-avatar {
    width: 26px;
    height: 26px;
    border-radius: 8px;
    flex-shrink: 0;
    margin-top: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ai-msg-avatar--user {
    background: var(--ai-user-bg);
    color: white;
}
.ai-msg-avatar--ai {
    background: var(--ai-accent-l2);
    color: var(--ai-accent);
}
.ai-msg-body {
    display: flex;
    flex-direction: column;
    gap: 4px;
    max-width: 84%;
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
    position: relative;
}
.ai-bubble--user {
    background: var(--ai-user-bg);
    color: white;
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
    gap: 6px;
    padding: 10px 14px;
}
.ai-typing-dots {
    display: flex;
    gap: 4px;
}
.ai-typing-dots span {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: var(--ai-accent);
    animation: typBounce 1.2s ease-in-out infinite;
}
.ai-typing-dots span:nth-child(2) {
    animation-delay: 0.2s;
}
.ai-typing-dots span:nth-child(3) {
    animation-delay: 0.4s;
}
@keyframes typBounce {
    0%,
    60%,
    100% {
        transform: translateY(0);
        opacity: 0.5;
    }
    30% {
        transform: translateY(-5px);
        opacity: 1;
    }
}
.ai-typing-label {
    font-size: 10px;
    color: var(--ai-text3);
    font-style: italic;
}
.ai-bubble-text :deep(strong) {
    font-weight: 700;
}
.ai-bubble-text :deep(.ai-md-heading) {
    font-size: 13px;
    font-weight: 700;
    margin: 4px 0 2px;
    color: var(--ai-accent);
}
.ai-bubble-text :deep(.ai-md-p) {
    margin: 4px 0;
}
.ai-bubble-text :deep(.ai-md-list) {
    margin: 4px 0 4px 14px;
    padding: 0;
}
.ai-bubble-text :deep(li) {
    margin: 2px 0;
    font-size: 11.5px;
}
.ai-bubble-text :deep(.ai-inline-code) {
    background: rgba(99, 102, 241, 0.12);
    color: var(--ai-accent);
    padding: 1px 5px;
    border-radius: 4px;
    font-size: 11px;
    font-family: "Fira Code", monospace;
}
.ai-bubble-text :deep(.ai-code-block) {
    background: var(--ai-code-bg);
    color: var(--ai-code-text);
    padding: 10px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-family: "Fira Code", monospace;
    overflow-x: auto;
    margin: 6px 0;
    border: 1px solid rgba(255, 255, 255, 0.06);
}
.ai-code-toolbar {
    display: flex;
    justify-content: flex-end;
    margin-top: 4px;
}
.ai-code-btn {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    font-size: 9px;
    font-weight: 600;
    border: 1px solid var(--ai-border);
    border-radius: 5px;
    background: var(--ai-bg);
    color: var(--ai-text3);
    cursor: pointer;
    font-family: inherit;
    transition: all 0.14s;
}
.ai-code-btn:hover {
    color: var(--ai-accent);
    border-color: var(--ai-accent);
}
.ai-actions-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-top: 6px;
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
    font-family: inherit;
    transition: all 0.14s;
}
.ai-action-pill--primary {
    background: var(--ai-accent-l2);
    border-color: var(--ai-accent);
}
.ai-action-pill:hover {
    background: var(--ai-accent);
    color: white;
    border-color: var(--ai-accent);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}
.ai-action-icon-svg {
    display: flex;
    align-items: center;
}
.ai-msg-time {
    font-size: 9px;
    color: var(--ai-text3);
    padding: 0 2px;
}
.ai-input-zone {
    border-top: 1px solid var(--ai-border);
    padding: 8px 10px 10px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex-shrink: 0;
    background: linear-gradient(to top, var(--ai-bg), transparent);
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
    transition: all 0.14s;
    flex-shrink: 0;
}
.ai-quick-pill:hover {
    border-color: var(--ai-accent);
    color: var(--ai-accent);
    background: var(--ai-accent-l);
}
.ai-input-box {
    position: relative;
    border: 1px solid var(--ai-border);
    border-radius: 12px;
    background: var(--ai-bg2);
    overflow: hidden;
    transition:
        border-color 0.15s,
        box-shadow 0.15s;
}
.ai-input-box--focus {
    border-color: var(--ai-accent);
    box-shadow: 0 0 0 3px var(--ai-accent-l2);
}
.ai-mode-strip {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    border-radius: 12px 12px 0 0;
    transition: background 0.3s;
}
.ai-mode-strip--generate {
    background: linear-gradient(90deg, #6366f1, #8b5cf6);
}
.ai-mode-strip--enhance {
    background: linear-gradient(90deg, #10b981, #06b6d4);
}
.ai-mode-strip--summarize {
    background: linear-gradient(90deg, #f59e0b, #ef4444);
}
.ai-mode-strip--chart {
    background: linear-gradient(90deg, #3b82f6, #06b6d4);
}
.ai-mode-strip--translate {
    background: linear-gradient(90deg, #ec4899, #8b5cf6);
}
.ai-mode-strip--image {
    background: linear-gradient(90deg, #f43f5e, #ec4899);
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
    min-height: 52px;
    max-height: 140px;
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
    transition: color 0.2s;
}
.ai-char-count--warn {
    color: #ef4444;
}
.ai-input-actions {
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
    transition: all 0.14s;
}
.ai-attach-btn:hover {
    background: var(--ai-accent-l);
    color: var(--ai-accent);
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
    transition: all 0.15s;
}
.ai-send-btn--ready {
    background: var(--ai-accent);
    color: white;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
}
.ai-send-btn--ready:hover {
    transform: scale(1.07);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.45);
}
.ai-send-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
.ai-spin {
    animation: spinAnim 0.8s linear infinite;
}
@keyframes spinAnim {
    to {
        transform: rotate(360deg);
    }
}
.ai-hint-bar {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 9px;
    color: var(--ai-text3);
}
.ai-hint-bar kbd {
    padding: 1px 4px;
    border-radius: 3px;
    background: var(--ai-bg3);
    border: 1px solid var(--ai-border2);
    font-family: inherit;
    font-size: 9px;
}
.ai-hint-sep {
    opacity: 0.4;
}
.ai-model-tag {
    margin-left: auto;
    background: linear-gradient(90deg, var(--ai-accent), var(--ai-accent2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700;
    font-size: 9px;
}
.ai-resize-handle {
    position: absolute;
    background: transparent;
}
.ai-resize-se {
    bottom: 0;
    right: 0;
    width: 18px;
    height: 18px;
    cursor: se-resize;
    background: linear-gradient(135deg, transparent 50%, var(--ai-border2) 50%);
    border-radius: 0 0 18px 0;
}
.ai-resize-se::after {
    content: "";
    position: absolute;
    right: 4px;
    bottom: 4px;
    width: 6px;
    height: 6px;
    border-right: 2px solid var(--ai-border2);
    border-bottom: 2px solid var(--ai-border2);
    border-radius: 0 0 2px 0;
}
.ai-emerge-enter-active {
    animation: aiEmerge 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}
.ai-emerge-leave-active {
    animation: aiEmerge 0.2s ease reverse;
}
@keyframes aiEmerge {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.94);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
.ai-collapse-enter-active {
    transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
}
.ai-collapse-leave-active {
    transition: all 0.18s ease;
}
.ai-collapse-enter-from,
.ai-collapse-leave-to {
    opacity: 0;
    transform: translateY(-8px);
    max-height: 0;
}
.ai-fade-enter-active {
    transition:
        opacity 0.3s,
        transform 0.3s;
}
.ai-fade-leave-active {
    transition: opacity 0.2s;
}
.ai-fade-enter-from,
.ai-fade-leave-to {
    opacity: 0;
    transform: translateY(6px);
}
.ai-msg-enter-active {
    transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
}
.ai-msg-enter-from {
    opacity: 0;
    transform: translateY(10px) scale(0.97);
}
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
    .ai-resize-se {
        display: none;
    }
    .ai-chips-grid {
        grid-template-columns: 1fr;
    }
    .ai-header {
        cursor: default;
    }
}
</style>
