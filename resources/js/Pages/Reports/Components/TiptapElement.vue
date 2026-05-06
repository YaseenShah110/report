<!--
  ╔══════════════════════════════════════════════════════════════════╗
  ║   TiptapElement.vue - Mind-Blowing Rich Text Editor            ║
  ║   Floating Toolbar · Slash Commands · AI · Markdown · Emoji    ║
  ╚══════════════════════════════════════════════════════════════════╝
-->
<template>
    <div class="tiptap-wrapper" ref="wrapperRef" @click.stop>
        <!-- ═══ FLOATING FORMATTING TOOLBAR ═══════════════════════════ -->
        <Teleport to="body">
            <Transition name="toolbar-fade">
                <div
                    v-if="showToolbar && props.editable"
                    class="tiptap-toolbar"
                    :style="toolbarStyle"
                    @mousedown.prevent
                >
                    <!-- Text Style Group -->
                    <div class="tb-group">
                        <button
                            @click="editor.chain().focus().toggleBold().run()"
                            :class="{ active: editor.isActive('bold') }"
                            title="Bold (Ctrl+B)"
                        >
                            <b>B</b>
                        </button>
                        <button
                            @click="editor.chain().focus().toggleItalic().run()"
                            :class="{ active: editor.isActive('italic') }"
                            title="Italic (Ctrl+I)"
                        >
                            <i>I</i>
                        </button>
                        <button
                            @click="
                                editor.chain().focus().toggleUnderline().run()
                            "
                            :class="{ active: editor.isActive('underline') }"
                            title="Underline (Ctrl+U)"
                        >
                            <u>U</u>
                        </button>
                        <button
                            @click="editor.chain().focus().toggleStrike().run()"
                            :class="{ active: editor.isActive('strike') }"
                            title="Strikethrough"
                        >
                            <s>S</s>
                        </button>
                        <button
                            @click="editor.chain().focus().toggleCode().run()"
                            :class="{ active: editor.isActive('code') }"
                            title="Inline Code"
                        >
                            <i class="fa-solid fa-code"></i>
                        </button>
                        <button
                            @click="
                                editor.chain().focus().toggleHighlight().run()
                            "
                            :class="{ active: editor.isActive('highlight') }"
                            title="Highlight"
                        >
                            <i class="fa-solid fa-highlighter"></i>
                        </button>
                    </div>

                    <div class="tb-divider"></div>

                    <!-- Heading Group -->
                    <div class="tb-group">
                        <button
                            @click="
                                editor
                                    .chain()
                                    .focus()
                                    .toggleHeading({ level: 1 })
                                    .run()
                            "
                            :class="{
                                active: editor.isActive('heading', {
                                    level: 1,
                                }),
                            }"
                            title="Heading 1"
                        >
                            <span class="tb-h"
                                >H<span class="tb-h-num">1</span></span
                            >
                        </button>
                        <button
                            @click="
                                editor
                                    .chain()
                                    .focus()
                                    .toggleHeading({ level: 2 })
                                    .run()
                            "
                            :class="{
                                active: editor.isActive('heading', {
                                    level: 2,
                                }),
                            }"
                            title="Heading 2"
                        >
                            <span class="tb-h"
                                >H<span class="tb-h-num">2</span></span
                            >
                        </button>
                        <button
                            @click="
                                editor
                                    .chain()
                                    .focus()
                                    .toggleHeading({ level: 3 })
                                    .run()
                            "
                            :class="{
                                active: editor.isActive('heading', {
                                    level: 3,
                                }),
                            }"
                            title="Heading 3"
                        >
                            <span class="tb-h"
                                >H<span class="tb-h-num">3</span></span
                            >
                        </button>
                        <button
                            @click="editor.chain().focus().setParagraph().run()"
                            :class="{ active: editor.isActive('paragraph') }"
                            title="Paragraph"
                        >
                            <i class="fa-solid fa-paragraph"></i>
                        </button>
                    </div>

                    <div class="tb-divider"></div>

                    <!-- List & Block Group -->
                    <div class="tb-group">
                        <button
                            @click="
                                editor.chain().focus().toggleBulletList().run()
                            "
                            :class="{ active: editor.isActive('bulletList') }"
                            title="Bullet List"
                        >
                            <i class="fa-solid fa-list-ul"></i>
                        </button>
                        <button
                            @click="
                                editor.chain().focus().toggleOrderedList().run()
                            "
                            :class="{ active: editor.isActive('orderedList') }"
                            title="Numbered List"
                        >
                            <i class="fa-solid fa-list-ol"></i>
                        </button>
                        <button
                            @click="
                                editor.chain().focus().toggleTaskList().run()
                            "
                            :class="{ active: editor.isActive('taskList') }"
                            title="Task List"
                        >
                            <i class="fa-solid fa-list-check"></i>
                        </button>
                        <button
                            @click="
                                editor.chain().focus().toggleBlockquote().run()
                            "
                            :class="{ active: editor.isActive('blockquote') }"
                            title="Blockquote"
                        >
                            <i class="fa-solid fa-quote-right"></i>
                        </button>
                        <button
                            @click="
                                editor.chain().focus().toggleCodeBlock().run()
                            "
                            :class="{ active: editor.isActive('codeBlock') }"
                            title="Code Block"
                        >
                            <i class="fa-solid fa-file-code"></i>
                        </button>
                    </div>

                    <div class="tb-divider"></div>

                    <!-- Alignment Group -->
                    <div class="tb-group">
                        <button
                            @click="
                                editor
                                    .chain()
                                    .focus()
                                    .setTextAlign('left')
                                    .run()
                            "
                            :class="{
                                active: editor.isActive({ textAlign: 'left' }),
                            }"
                            title="Align Left"
                        >
                            <i class="fa-solid fa-align-left"></i>
                        </button>
                        <button
                            @click="
                                editor
                                    .chain()
                                    .focus()
                                    .setTextAlign('center')
                                    .run()
                            "
                            :class="{
                                active: editor.isActive({
                                    textAlign: 'center',
                                }),
                            }"
                            title="Align Center"
                        >
                            <i class="fa-solid fa-align-center"></i>
                        </button>
                        <button
                            @click="
                                editor
                                    .chain()
                                    .focus()
                                    .setTextAlign('right')
                                    .run()
                            "
                            :class="{
                                active: editor.isActive({ textAlign: 'right' }),
                            }"
                            title="Align Right"
                        >
                            <i class="fa-solid fa-align-right"></i>
                        </button>
                        <button
                            @click="
                                editor
                                    .chain()
                                    .focus()
                                    .setTextAlign('justify')
                                    .run()
                            "
                            :class="{
                                active: editor.isActive({
                                    textAlign: 'justify',
                                }),
                            }"
                            title="Justify"
                        >
                            <i class="fa-solid fa-align-justify"></i>
                        </button>
                    </div>

                    <div class="tb-divider"></div>

                    <!-- Insert Group -->
                    <div class="tb-group">
                        <button @click="addLink" title="Insert Link">
                            <i class="fa-solid fa-link"></i>
                        </button>
                        <button @click="addImage" title="Insert Image">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button @click="addTable" title="Insert Table">
                            <i class="fa-solid fa-table"></i>
                        </button>
                        <button
                            @click="
                                editor.chain().focus().setHorizontalRule().run()
                            "
                            title="Horizontal Rule"
                        >
                            <i class="fa-solid fa-grip-lines"></i>
                        </button>
                        <button @click="addEmoji" title="Insert Emoji">
                            😊
                        </button>
                    </div>

                    <div class="tb-divider"></div>

                    <!-- Undo/Redo -->
                    <div class="tb-group">
                        <button
                            @click="editor.chain().focus().undo().run()"
                            :disabled="!editor.can().undo()"
                            title="Undo (Ctrl+Z)"
                        >
                            <i class="fa-solid fa-undo"></i>
                        </button>
                        <button
                            @click="editor.chain().focus().redo().run()"
                            :disabled="!editor.can().redo()"
                            title="Redo (Ctrl+Y)"
                        >
                            <i class="fa-solid fa-redo"></i>
                        </button>
                        <button
                            @click="
                                editor
                                    .chain()
                                    .focus()
                                    .clearNodes()
                                    .unsetAllMarks()
                                    .run()
                            "
                            title="Clear Formatting"
                        >
                            <i class="fa-solid fa-eraser"></i>
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ═══ SLASH COMMAND MENU ═══════════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="toolbar-fade">
                <div
                    v-if="slashMenuVisible"
                    class="tiptap-slash-menu"
                    :style="slashMenuStyle"
                    @mousedown.prevent
                >
                    <div class="slash-header">Insert Block</div>
                    <button
                        v-for="cmd in filteredSlashCommands"
                        :key="cmd.id"
                        @click="executeSlashCommand(cmd)"
                        class="slash-item"
                    >
                        <span class="slash-icon"
                            ><i :class="cmd.icon"></i
                        ></span>
                        <div class="slash-info">
                            <span class="slash-label">{{ cmd.label }}</span>
                            <span class="slash-desc">{{
                                cmd.description
                            }}</span>
                        </div>
                        <kbd v-if="cmd.shortcut" class="slash-kbd">{{
                            cmd.shortcut
                        }}</kbd>
                    </button>
                </div>
            </Transition>
        </Teleport>

        <!-- ═══ EMOJI PICKER ═══════════════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="toolbar-fade">
                <div
                    v-if="emojiPickerVisible"
                    class="tiptap-emoji-picker"
                    :style="emojiPickerStyle"
                    @mousedown.prevent
                >
                    <div class="emoji-header">Emoji Picker</div>
                    <div class="emoji-search-wrap">
                        <input
                            v-model="emojiSearch"
                            class="emoji-search-input"
                            placeholder="Search emoji..."
                            @keydown.escape="emojiPickerVisible = false"
                        />
                    </div>
                    <div class="emoji-grid">
                        <button
                            v-for="emoji in filteredEmojis"
                            :key="emoji"
                            @click="insertEmoji(emoji)"
                            class="emoji-btn"
                            :title="emoji"
                        >
                            {{ emoji }}
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ═══ COLOR PICKER FOR TEXT ═══════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="toolbar-fade">
                <div
                    v-if="colorPickerVisible"
                    class="tiptap-color-picker"
                    :style="colorPickerStyle"
                    @mousedown.prevent
                >
                    <div class="color-header">Text Color</div>
                    <div class="color-grid">
                        <button
                            v-for="c in textColors"
                            :key="c"
                            @click="
                                applyTextColor(c);
                                colorPickerVisible = false;
                            "
                            class="color-btn"
                            :style="{ background: c }"
                            :title="c"
                        ></button>
                    </div>
                    <div class="color-custom">
                        <input
                            type="color"
                            @input="applyTextColor($event.target.value)"
                            class="color-custom-input"
                        />
                        <span>Custom</span>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ═══ EDITOR CONTENT ═══════════════════════════════════════════ -->
        <div class="tiptap-editor" ref="editorRef"></div>

        <!-- ═══ BOTTOM ACTIONS ═══════════════════════════════════════ -->
        <div v-if="props.editable" class="tiptap-bottom-actions">
            <button
                @click="askAI"
                class="tiptap-ai-btn"
                title="AI Assist (Select text for better results)"
            >
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <span>AI</span>
            </button>
            <button
                @click="colorPickerVisible = !colorPickerVisible"
                class="tiptap-bottom-btn"
                title="Text Color"
            >
                <i class="fa-solid fa-palette"></i>
            </button>
            <button
                @click="showEditorStats"
                class="tiptap-bottom-btn"
                title="Stats"
            >
                <i class="fa-solid fa-chart-simple"></i>
            </button>
            <span class="tiptap-word-count" :title="charCount + ' characters'"
                >{{ wordCount }} words</span
            >
        </div>

        <!-- ═══ AI LOADING OVERLAY ═══════════════════════════════════ -->
        <div v-if="aiLoading" class="tiptap-ai-overlay">
            <div class="tiptap-ai-spinner">
                <i class="fa-solid fa-wand-magic-sparkles fa-spin"></i>
                <span>AI is thinking...</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import {
    ref,
    watch,
    onMounted,
    onBeforeUnmount,
    nextTick,
    computed,
} from "vue";
import { Editor } from "@tiptap/core";
import StarterKit from "@tiptap/starter-kit";
import Underline from "@tiptap/extension-underline";
import Link from "@tiptap/extension-link";
import Highlight from "@tiptap/extension-highlight";
import Placeholder from "@tiptap/extension-placeholder";
import TextAlign from "@tiptap/extension-text-align";
import Image from "@tiptap/extension-image";
import TaskList from "@tiptap/extension-task-list";
import TaskItem from "@tiptap/extension-task-item";
import { Table } from "@tiptap/extension-table"
import { TableRow } from "@tiptap/extension-table-row"
import { TableCell } from "@tiptap/extension-table-cell"
import { TableHeader } from "@tiptap/extension-table-header"
import {TextStyle} from "@tiptap/extension-text-style";
import { Color } from "@tiptap/extension-color";
import Strike from "@tiptap/extension-strike";

const props = defineProps({
    content: { type: String, default: "" },
    editable: { type: Boolean, default: false },
});

const emit = defineEmits(["update:content", "ai-request"]);

// ═══ REFS ═══════════════════════════════════════════════════════════
const editorRef = ref(null);
const wrapperRef = ref(null);
let editorInstance = null;

// ═══ UI STATE ═════════════════════════════════════════════════════
const showToolbar = ref(false);
const toolbarStyle = ref({});
const slashMenuVisible = ref(false);
const slashMenuStyle = ref({});
const emojiPickerVisible = ref(false);
const emojiPickerStyle = ref({});
const colorPickerVisible = ref(false);
const colorPickerStyle = ref({});
const aiLoading = ref(false);
const emojiSearch = ref("");
const slashSearch = ref("");

// ═══ COMPUTED ══════════════════════════════════════════════════════
const editor = computed(() => editorInstance);
const wordCount = computed(
    () => editorInstance?.storage?.characterCount?.words?.() || 0,
);
const charCount = computed(
    () => editorInstance?.storage?.characterCount?.characters?.() || 0,
);

// ═══ SLASH COMMANDS ════════════════════════════════════════════════
const slashCommands = [
    {
        id: "h1",
        label: "Heading 1",
        description: "Large section heading",
        icon: "fa-solid fa-h",
        shortcut: "# + Space",
        action: () =>
            editorInstance?.chain().focus().toggleHeading({ level: 1 }).run(),
    },
    {
        id: "h2",
        label: "Heading 2",
        description: "Medium section heading",
        icon: "fa-solid fa-h",
        shortcut: "## + Space",
        action: () =>
            editorInstance?.chain().focus().toggleHeading({ level: 2 }).run(),
    },
    {
        id: "h3",
        label: "Heading 3",
        description: "Small section heading",
        icon: "fa-solid fa-h",
        shortcut: "### + Space",
        action: () =>
            editorInstance?.chain().focus().toggleHeading({ level: 3 }).run(),
    },
    {
        id: "bullet",
        label: "Bullet List",
        description: "Unordered list items",
        icon: "fa-solid fa-list-ul",
        shortcut: "- + Space",
        action: () => editorInstance?.chain().focus().toggleBulletList().run(),
    },
    {
        id: "ordered",
        label: "Numbered List",
        description: "Ordered list items",
        icon: "fa-solid fa-list-ol",
        shortcut: "1. + Space",
        action: () => editorInstance?.chain().focus().toggleOrderedList().run(),
    },
    {
        id: "task",
        label: "Task List",
        description: "Checkable task items",
        icon: "fa-solid fa-list-check",
        action: () => editorInstance?.chain().focus().toggleTaskList().run(),
    },
    {
        id: "quote",
        label: "Blockquote",
        description: "Quoted text block",
        icon: "fa-solid fa-quote-right",
        shortcut: "> + Space",
        action: () => editorInstance?.chain().focus().toggleBlockquote().run(),
    },
    {
        id: "code",
        label: "Code Block",
        description: "Code snippet with syntax",
        icon: "fa-solid fa-file-code",
        shortcut: "``` + Enter",
        action: () => editorInstance?.chain().focus().toggleCodeBlock().run(),
    },
    {
        id: "table",
        label: "Table",
        description: "Insert a data table",
        icon: "fa-solid fa-table",
        action: () =>
            editorInstance
                ?.chain()
                .focus()
                .insertTable({ rows: 3, cols: 3, withHeaderRow: true })
                .run(),
    },
    {
        id: "image",
        label: "Image",
        description: "Insert an image",
        icon: "fa-solid fa-image",
        action: addImage,
    },
    {
        id: "divider",
        label: "Divider",
        description: "Horizontal rule",
        icon: "fa-solid fa-grip-lines",
        shortcut: "--- + Enter",
        action: () => editorInstance?.chain().focus().setHorizontalRule().run(),
    },
    {
        id: "link",
        label: "Link",
        description: "Add a hyperlink",
        icon: "fa-solid fa-link",
        action: addLink,
    },
    {
        id: "emoji",
        label: "Emoji",
        description: "Insert emoji",
        icon: "fa-solid fa-face-smile",
        action: () => (emojiPickerVisible.value = true),
    },
];

const filteredSlashCommands = computed(() => {
    if (!slashSearch.value) return slashCommands;
    const q = slashSearch.value.toLowerCase();
    return slashCommands.filter(
        (c) =>
            c.label.toLowerCase().includes(q) ||
            c.description.toLowerCase().includes(q),
    );
});

// ═══ EMOJIS ════════════════════════════════════════════════════════
const emojis = [
    "😀",
    "😃",
    "😄",
    "😁",
    "😅",
    "😂",
    "🤣",
    "😊",
    "😇",
    "🙂",
    "😍",
    "🥰",
    "😘",
    "😗",
    "😋",
    "😛",
    "😜",
    "🤪",
    "😝",
    "🤑",
    "🤗",
    "🤭",
    "🤫",
    "🤔",
    "🤐",
    "😐",
    "😑",
    "😶",
    "😏",
    "😒",
    "🙄",
    "😬",
    "😮",
    "😯",
    "😲",
    "😳",
    "🥺",
    "😢",
    "😭",
    "😤",
    "😡",
    "🤬",
    "😈",
    "👿",
    "💀",
    "☠️",
    "💩",
    "🤡",
    "👻",
    "👽",
    "🤖",
    "🎃",
    "😺",
    "😸",
    "😹",
    "😻",
    "😼",
    "😽",
    "🙀",
    "😿",
    "😾",
    "💋",
    "💌",
    "💘",
    "💝",
    "💖",
    "💗",
    "💓",
    "💞",
    "💕",
    "💟",
    "❣️",
    "💔",
    "❤️",
    "🧡",
    "💛",
    "💚",
    "💙",
    "💜",
    "🤎",
    "🖤",
    "🤍",
    "💯",
    "💢",
    "💥",
    "💫",
    "💦",
    "💨",
    "🕳️",
    "💣",
    "💬",
    "👁️‍🗨️",
    "🗨️",
    "🗯️",
    "💭",
    "💤",
    "👍",
    "👎",
    "👏",
    "🙌",
    "🤝",
    "💪",
    "✍️",
    "🙏",
    "💅",
    "🎯",
    "🏆",
    "🥇",
    "🥈",
    "🥉",
    "🎖️",
    "🏅",
    "📊",
    "📈",
    "📉",
    "💼",
    "📁",
    "📂",
    "🗂️",
    "📋",
    "📌",
    "📍",
    "✂️",
    "🔒",
    "🔓",
    "🔑",
    "⚡",
    "🔥",
    "⭐",
    "🌟",
    "✨",
    "💡",
    "🎵",
    "🎶",
    "🌈",
    "☀️",
    "🌙",
    "🌍",
    "🏠",
    "🏢",
    "🚀",
    "✈️",
    "🚗",
    "⏰",
    "📅",
    "🔔",
    "🎁",
    "💎",
];

const filteredEmojis = computed(() => {
    if (!emojiSearch.value) return emojis.slice(0, 60);
    return emojis.filter((e) => e.includes(emojiSearch.value));
});

// ═══ TEXT COLORS ══════════════════════════════════════════════════
const textColors = [
    "#0f172a",
    "#475569",
    "#94a3b8",
    "#6366f1",
    "#8b5cf6",
    "#ec4899",
    "#ef4444",
    "#f59e0b",
    "#10b981",
    "#06b6d4",
    "#1e40af",
    "#047857",
    "#b45309",
    "#be123c",
    "#6d28d9",
    "#ffffff",
    "#000000",
];

// ═══ INIT EDITOR ═══════════════════════════════════════════════════
function initEditor() {
    if (!editorRef.value) return;

    if (editorInstance) {
        editorInstance.destroy();
        editorInstance = null;
    }

    editorInstance = new Editor({
        element: editorRef.value,
        content: props.content || "<p></p>",
        editable: props.editable,
        extensions: [
            StarterKit.configure({
                heading: { levels: [1, 2, 3] },
                codeBlock: { HTMLAttributes: { class: "tiptap-code-block" } },
            }),
            Underline,
            Strike,
            Link.configure({
                openOnClick: false,
                HTMLAttributes: { class: "tiptap-link" },
                autolink: true,
            }),
            Highlight.configure({ multicolor: true }),
            Placeholder.configure({
                placeholder: 'Type "/" for commands, or start typing...',
            }),
            TextAlign.configure({
                types: ["heading", "paragraph"],
                alignments: ["left", "center", "right", "justify"],
            }),
            Image.configure({
                inline: false,
                allowBase64: true,
                HTMLAttributes: { class: "tiptap-image" },
            }),
            TaskList,
            TaskItem.configure({
                nested: true,
                HTMLAttributes: { class: "tiptap-task-item" },
            }),
            Table.configure({
                resizable: true,
                HTMLAttributes: { class: "tiptap-table" },
            }),
            TableRow,
            TableCell,
            TableHeader,
            TextStyle,
            Color,
        ],
        onUpdate: ({ editor }) => {
            emit("update:content", editor.getHTML());
        },
        onSelectionUpdate: ({ editor }) => {
            updateToolbarPosition(editor);
        },
        onBlur: () => {
            setTimeout(() => {
                showToolbar.value = false;
            }, 200);
        },
        onFocus: ({ editor }) => {
            if (!editor.state.selection.empty) updateToolbarPosition(editor);
        },
        editorProps: {
            attributes: {
                class: "tiptap-prose",
                spellcheck: "true",
            },
            handleKeyDown: (view, event) => {
                if (event.key === "/") {
                    setTimeout(() => positionSlashMenu(), 100);
                    return false;
                }
                if (event.key === "Escape") {
                    slashMenuVisible.value = false;
                    emojiPickerVisible.value = false;
                    colorPickerVisible.value = false;
                    return false;
                }
                if (event.key === "Tab" && !event.shiftKey) {
                    // Indent on tab
                    return false;
                }
                return false;
            },
            handlePaste: (view, event) => {
                // Clean pasted content
                return false;
            },
        },
    });

    if (props.editable) {
        setTimeout(() => editorInstance?.commands.focus("end"), 100);
    }
}

// ═══ TOOLBAR POSITION ═════════════════════════════════════════════
function updateToolbarPosition(ed) {
    if (!ed || !props.editable) return;
    const { from, to, empty } = ed.state.selection;
    if (empty) {
        showToolbar.value = false;
        return;
    }

    const { view } = ed;
    const start = view.coordsAtPos(from);
    const end = view.coordsAtPos(to);
    const wrapperRect = wrapperRef.value?.getBoundingClientRect();
    if (!wrapperRect) return;

    const top = start.top - wrapperRect.top - 56;
    const left = (start.left + end.left) / 2 - wrapperRect.left - 250;

    toolbarStyle.value = {
        top: `${Math.max(top, 0)}px`,
        left: `${Math.max(left, 0)}px`,
    };
    showToolbar.value = true;
}

// ═══ SLASH MENU ══════════════════════════════════════════════════
function positionSlashMenu() {
    if (!editorInstance) return;
    const { view } = editorInstance;
    if (!view) return;
    const { from } = view.state.selection;
    const coords = view.coordsAtPos(from);
    const wrapperRect = wrapperRef.value?.getBoundingClientRect();
    if (wrapperRect) {
        slashMenuStyle.value = {
            top: `${coords.top - wrapperRect.top + 24}px`,
            left: `${coords.left - wrapperRect.left}px`,
        };
        slashMenuVisible.value = true;
        slashSearch.value = "";
        nextTick(() => {
            const input = document.querySelector(".slash-search-input");
            input?.focus();
        });
    }
}

function executeSlashCommand(cmd) {
    cmd.action();
    slashMenuVisible.value = false;
    slashSearch.value = "";
}

// ═══ EMOJI ═══════════════════════════════════════════════════════
function addEmoji() {
    if (!editorInstance) return;
    const wrapperRect = wrapperRef.value?.getBoundingClientRect();
    if (wrapperRect) {
        emojiPickerStyle.value = {
            top: `${Math.max(0, wrapperRect.height - 320)}px`,
            left: "0px",
        };
        emojiPickerVisible.value = true;
        emojiSearch.value = "";
    }
}

function insertEmoji(emoji) {
    editorInstance?.chain().focus().insertContent(emoji).run();
    emojiPickerVisible.value = false;
}

// ═══ LINK ════════════════════════════════════════════════════════
function addLink() {
    const previousUrl = editorInstance?.getAttributes("link").href || "";
    const url = prompt("Enter URL:", previousUrl);
    if (url === null) return;
    if (url === "") {
        editorInstance
            ?.chain()
            .focus()
            .extendMarkRange("link")
            .unsetLink()
            .run();
        return;
    }
    editorInstance
        ?.chain()
        .focus()
        .extendMarkRange("link")
        .setLink({ href: url })
        .run();
}

// ═══ IMAGE ═══════════════════════════════════════════════════════
function addImage() {
    const url = prompt("Enter image URL:");
    if (url) {
        editorInstance
            ?.chain()
            .focus()
            .setImage({ src: url, alt: "Image" })
            .run();
    }
}

// ═══ TABLE ═══════════════════════════════════════════════════════
function addTable() {
    editorInstance
        ?.chain()
        .focus()
        .insertTable({ rows: 3, cols: 3, withHeaderRow: true })
        .run();
}

// ═══ COLOR ═══════════════════════════════════════════════════════
function applyTextColor(color) {
    editorInstance?.chain().focus().setColor(color).run();
    colorPickerVisible.value = false;
}

// ═══ AI ═════════════════════════════════════════════════════════
async function askAI() {
    if (!editorInstance) return;
    const selection = editorInstance.state.selection;
    const selectedText = selection.empty
        ? ""
        : editorInstance.state.doc.textBetween(selection.from, selection.to);

    let prompt;
    if (selectedText) {
        const action = prompt(
            "What should AI do?\n\n1. Enhance\n2. Make shorter\n3. Make longer\n4. Fix grammar\n5. Change tone to professional\n\nEnter number or custom instruction:",
            "1",
        );
        const actions = {
            1: `Enhance this text professionally: "${selectedText}"`,
            2: `Make this text more concise: "${selectedText}"`,
            3: `Expand this text with more detail: "${selectedText}"`,
            4: `Fix grammar and spelling: "${selectedText}"`,
            5: `Rewrite in professional business tone: "${selectedText}"`,
        };
        prompt = actions[action] || action;
    } else {
        prompt = prompt("What should AI write about?");
    }

    if (!prompt) return;

    aiLoading.value = true;

    try {
        const csrf =
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content") || "";
        const res = await fetch("/api/ai/generate", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf,
                Accept: "application/json",
            },
            body: JSON.stringify({ prompt, type: "text" }),
        });
        const data = await res.json();

        if (data.result && editorInstance) {
            if (selectedText) {
                editorInstance
                    .chain()
                    .focus()
                    .deleteSelection()
                    .insertContent(data.result)
                    .run();
            } else {
                editorInstance.chain().focus().insertContent(data.result).run();
            }
        }
    } catch (e) {
        console.warn("AI failed:", e);
    }

    aiLoading.value = false;
}

// ═══ STATS ═══════════════════════════════════════════════════════
function showEditorStats() {
    if (!editorInstance) return;
    const chars = editorInstance.storage?.characterCount?.characters?.() || 0;
    const words = editorInstance.storage?.characterCount?.words?.() || 0;
    alert(
        `Word Count: ${words}\nCharacter Count: ${chars}\n\nReading Time: ~${Math.max(1, Math.ceil(words / 200))} min`,
    );
}

// ═══ WATCHERS ════════════════════════════════════════════════════
watch(
    () => props.editable,
    (val) => {
        if (editorInstance) {
            editorInstance.setEditable(val);
            if (val)
                setTimeout(() => editorInstance?.commands?.focus("end"), 100);
        }
    },
);

watch(
    () => props.content,
    (val) => {
        if (editorInstance && val !== editorInstance.getHTML()) {
            editorInstance.commands.setContent(val || "<p></p>", false);
        }
    },
);

// ═══ LIFECYCLE ════════════════════════════════════════════════════
onMounted(() => {
    nextTick(() => initEditor());
});
onBeforeUnmount(() => {
    if (editorInstance) {
        editorInstance.destroy();
        editorInstance = null;
    }
});
</script>

<style>
/* ═══ WRAPPER ══════════════════════════════════════════════════ */
.tiptap-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
    background: var(--bg-primary, #ffffff);
    border-radius: inherit;
}

/* ═══ EDITOR ══════════════════════════════════════════════════ */
.tiptap-editor {
    width: 100%;
    height: 100%;
    overflow-y: auto;
    padding: 12px 16px;
}

.tiptap-prose {
    outline: none;
    min-height: 100%;
    font-family: inherit;
    font-size: inherit;
    color: inherit;
    line-height: 1.75;
    letter-spacing: inherit;
}

.tiptap-prose p {
    margin: 0 0 10px;
}
.tiptap-prose h1 {
    font-size: 2em;
    font-weight: 700;
    margin: 20px 0 10px;
    color: inherit;
    line-height: 1.3;
}
.tiptap-prose h2 {
    font-size: 1.5em;
    font-weight: 600;
    margin: 16px 0 8px;
    color: inherit;
    line-height: 1.35;
}
.tiptap-prose h3 {
    font-size: 1.2em;
    font-weight: 600;
    margin: 12px 0 6px;
    color: inherit;
    line-height: 1.4;
}
.tiptap-prose ul,
.tiptap-prose ol {
    padding-left: 24px;
    margin: 0 0 12px;
}
.tiptap-prose li {
    margin-bottom: 4px;
}
.tiptap-prose li p {
    margin: 0;
}
.tiptap-prose ul[data-type="taskList"] {
    list-style: none;
    padding-left: 0;
}
.tiptap-prose ul[data-type="taskList"] li {
    display: flex;
    align-items: flex-start;
    gap: 8px;
}
.tiptap-prose ul[data-type="taskList"] li label {
    flex-shrink: 0;
    margin-top: 3px;
}
.tiptap-prose ul[data-type="taskList"] li div {
    flex: 1;
}
.tiptap-prose blockquote {
    border-left: 3px solid #6366f1;
    padding: 10px 16px;
    margin: 14px 0;
    color: #64748b;
    background: rgba(99, 102, 241, 0.04);
    border-radius: 0 6px 6px 0;
    font-style: italic;
}
.tiptap-prose code {
    background: #f1f5f9;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: "Fira Code", "JetBrains Mono", monospace;
    font-size: 0.9em;
    color: #6366f1;
}
.tiptap-prose pre {
    background: #1e293b;
    color: #34d399;
    padding: 16px;
    border-radius: 10px;
    overflow-x: auto;
    margin: 14px 0;
    position: relative;
}
.tiptap-prose pre code {
    background: none;
    padding: 0;
    color: inherit;
    font-size: 13px;
    line-height: 1.6;
}
.tiptap-prose pre::before {
    content: attr(data-language);
    position: absolute;
    top: 8px;
    right: 12px;
    font-size: 10px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.tiptap-prose img.tiptap-image {
    max-width: 100%;
    height: auto;
    border-radius: 10px;
    margin: 10px 0;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}
.tiptap-prose img.tiptap-image:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    transform: scale(1.01);
}
.tiptap-prose a.tiptap-link {
    color: #6366f1;
    text-decoration: underline;
    cursor: pointer;
    transition: color 0.15s;
}
.tiptap-prose a.tiptap-link:hover {
    color: #4f46e5;
}
.tiptap-prose mark {
    background: #fef3c7;
    padding: 2px 6px;
    border-radius: 3px;
}
.tiptap-prose hr {
    border: none;
    border-top: 2px solid #e2e8f0;
    margin: 24px 0;
}
.tiptap-prose s {
    text-decoration: line-through;
}
.tiptap-prose p.is-editor-empty:first-child::before {
    color: #94a3b8;
    content: attr(data-placeholder);
    float: left;
    height: 0;
    pointer-events: none;
    font-style: italic;
}
.tiptap-prose .tiptap-table {
    width: 100%;
    border-collapse: collapse;
    margin: 12px 0;
}
.tiptap-prose .tiptap-table th,
.tiptap-prose .tiptap-table td {
    border: 1px solid #e2e8f0;
    padding: 8px 12px;
    text-align: left;
    min-width: 80px;
}
.tiptap-prose .tiptap-table th {
    background: #f8fafc;
    font-weight: 600;
    font-size: 12px;
}
.tiptap-prose .tiptap-table td {
    font-size: 12px;
}
.tiptap-prose .tiptap-table .selectedCell {
    background: rgba(99, 102, 241, 0.08);
}
.tiptap-prose ul[data-type="taskList"] li[data-checked="true"] > div > p {
    text-decoration: line-through;
    opacity: 0.5;
}

/* ═══ FLOATING TOOLBAR ════════════════════════════════════════ */
.tiptap-toolbar {
    position: absolute;
    z-index: 200;
    display: flex;
    align-items: center;
    gap: 3px;
    background: var(--bg-panel, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 14px;
    padding: 6px 8px;
    box-shadow:
        0 12px 40px rgba(0, 0, 0, 0.15),
        0 2px 8px rgba(0, 0, 0, 0.08);
    backdrop-filter: blur(12px);
    flex-wrap: wrap;
    max-width: 520px;
}

.tb-group {
    display: flex;
    align-items: center;
    gap: 2px;
}

.tb-group button {
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    border-radius: 8px;
    cursor: pointer;
    color: var(--text-secondary, #475569);
    font-size: 13px;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.12s;
}
.tb-group button:hover {
    background: var(--bg-secondary, #f8fafc);
    color: var(--text-primary, #0f172a);
}
.tb-group button.active {
    background: var(--accent-light, rgba(99, 102, 241, 0.1));
    color: var(--accent, #6366f1);
    font-weight: 700;
}
.tb-group button:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.tb-h {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: -0.02em;
    position: relative;
}
.tb-h-num {
    font-size: 7px;
    position: absolute;
    top: -2px;
    right: -6px;
}

.tb-divider {
    width: 1px;
    height: 22px;
    background: var(--border, #e2e8f0);
    margin: 0 4px;
    flex-shrink: 0;
}

/* ═══ SLASH MENU ═════════════════════════════════════════════ */
.tiptap-slash-menu {
    position: absolute;
    z-index: 300;
    background: var(--bg-panel, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 14px;
    padding: 8px;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    min-width: 280px;
    max-height: 340px;
    overflow-y: auto;
}
.slash-header {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--text-muted, #94a3b8);
    padding: 6px 10px 8px;
}
.slash-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 9px 10px;
    border: none;
    background: transparent;
    border-radius: 8px;
    cursor: pointer;
    text-align: left;
    transition: all 0.1s;
    font-family: inherit;
}
.slash-item:hover {
    background: var(--bg-secondary, #f8fafc);
}
.slash-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: var(--accent-light, rgba(99, 102, 241, 0.08));
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--accent, #6366f1);
    font-size: 13px;
    flex-shrink: 0;
}
.slash-info {
    flex: 1;
    min-width: 0;
}
.slash-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-primary, #0f172a);
}
.slash-desc {
    display: block;
    font-size: 10px;
    color: var(--text-muted, #94a3b8);
    margin-top: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.slash-kbd {
    font-size: 9px;
    color: var(--text-muted, #94a3b8);
    background: var(--bg-secondary, #f8fafc);
    padding: 2px 6px;
    border-radius: 4px;
    border: 1px solid var(--border, #e2e8f0);
    font-weight: 500;
    flex-shrink: 0;
}

/* ═══ EMOJI PICKER ══════════════════════════════════════════ */
.tiptap-emoji-picker {
    position: absolute;
    z-index: 300;
    background: var(--bg-panel, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 14px;
    padding: 10px;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    width: 300px;
    max-height: 320px;
    display: flex;
    flex-direction: column;
}
.emoji-header {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--text-muted, #94a3b8);
    margin-bottom: 6px;
}
.emoji-search-wrap {
    margin-bottom: 8px;
}
.emoji-search-input {
    width: 100%;
    padding: 6px 10px;
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 8px;
    background: var(--bg-secondary, #f8fafc);
    color: var(--text-primary, #0f172a);
    font-size: 12px;
    outline: none;
    box-sizing: border-box;
    font-family: inherit;
}
.emoji-search-input:focus {
    border-color: var(--accent, #6366f1);
}
.emoji-grid {
    display: grid;
    grid-template-columns: repeat(10, 1fr);
    gap: 3px;
    overflow-y: auto;
}
.emoji-btn {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid transparent;
    border-radius: 6px;
    background: transparent;
    cursor: pointer;
    font-size: 18px;
    transition: all 0.1s;
    padding: 0;
}
.emoji-btn:hover {
    background: var(--bg-secondary, #f8fafc);
    border-color: var(--border, #e2e8f0);
    transform: scale(1.15);
}

/* ═══ COLOR PICKER ══════════════════════════════════════════ */
.tiptap-color-picker {
    position: absolute;
    z-index: 300;
    background: var(--bg-panel, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 14px;
    padding: 10px;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    width: 200px;
}
.color-header {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--text-muted, #94a3b8);
    margin-bottom: 8px;
}
.color-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 4px;
    margin-bottom: 8px;
}
.color-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 2px solid var(--border, #e2e8f0);
    cursor: pointer;
    transition: all 0.1s;
}
.color-btn:hover {
    transform: scale(1.1);
    border-color: var(--text-primary, #0f172a);
}
.color-custom {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    color: var(--text-muted, #94a3b8);
}
.color-custom-input {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 2px solid var(--border, #e2e8f0);
    cursor: pointer;
    padding: 2px;
}

/* ═══ BOTTOM ACTIONS ════════════════════════════════════════ */
.tiptap-bottom-actions {
    position: absolute;
    bottom: 6px;
    right: 8px;
    display: flex;
    align-items: center;
    gap: 4px;
    z-index: 10;
    background: var(--bg-panel, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 10px;
    padding: 4px 6px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}
.tiptap-ai-btn {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 5px 10px;
    border: none;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff;
    border-radius: 7px;
    cursor: pointer;
    font-size: 10px;
    font-weight: 700;
    transition: all 0.2s;
    font-family: inherit;
    box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
}
.tiptap-ai-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(99, 102, 241, 0.4);
}
.tiptap-ai-btn i {
    font-size: 10px;
}
.tiptap-bottom-btn {
    width: 28px;
    height: 28px;
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 6px;
    background: transparent;
    cursor: pointer;
    color: var(--text-muted, #94a3b8);
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.12s;
}
.tiptap-bottom-btn:hover {
    background: var(--bg-secondary, #f8fafc);
    color: var(--text-primary, #0f172a);
}
.tiptap-word-count {
    font-size: 9px;
    color: var(--text-muted, #94a3b8);
    font-weight: 500;
    padding: 0 4px;
    white-space: nowrap;
}

/* ═══ AI OVERLAY ════════════════════════════════════════════ */
.tiptap-ai-overlay {
    position: absolute;
    inset: 0;
    z-index: 50;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(2px);
    border-radius: inherit;
}
.tiptap-ai-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: #6366f1;
    font-size: 13px;
    font-weight: 600;
}
.tiptap-ai-spinner i {
    font-size: 24px;
}

/* ═══ TRANSITIONS ══════════════════════════════════════════ */
.toolbar-fade-enter-active {
    animation: tfIn 0.15s ease;
}
.toolbar-fade-leave-active {
    animation: tfIn 0.1s ease reverse;
}
@keyframes tfIn {
    from {
        opacity: 0;
        transform: translateY(4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ═══ DARK MODE ════════════════════════════════════════════ */
.dark .tiptap-wrapper {
    background: var(--bg-primary, #1a2236);
}
.dark .tiptap-prose code {
    background: #1e293b;
}
.dark .tiptap-prose pre {
    background: #0f172a;
}
.dark .tiptap-prose blockquote {
    background: rgba(99, 102, 241, 0.06);
}
.dark .tiptap-ai-overlay {
    background: rgba(26, 34, 54, 0.8);
}
.dark .tiptap-prose .tiptap-table th {
    background: #1a2236;
}
.dark .tiptap-prose .tiptap-table td {
    border-color: #263348;
}
</style>
