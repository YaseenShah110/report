<!--
  ╔══════════════════════════════════════════════════════════════════╗
  ║   ShortcutOverlay.vue - Keyboard Shortcuts Reference (Ctrl+/)  ║
  ╚══════════════════════════════════════════════════════════════════╝
-->
<template>
  <Teleport to="body">
    <div class="shortcut-backdrop" @click="$emit('close')">
      <div class="shortcut-dialog" @click.stop>
        <div class="shortcut-header">
          <h2><i class="fa-solid fa-keyboard"></i> Keyboard Shortcuts</h2>
          <button @click="$emit('close')" class="shortcut-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <div class="shortcut-grid">
          <div v-for="(group, gidx) in shortcutGroups" :key="gidx" class="shortcut-group">
            <h3 class="shortcut-group-title">{{ group.title }}</h3>
            <div v-for="(sc, sidx) in group.shortcuts" :key="sidx" class="shortcut-row">
              <span class="shortcut-label">{{ sc.label }}</span>
              <div class="shortcut-keys">
                <kbd v-for="(key, kidx) in sc.keys" :key="kidx">{{ key }}</kbd>
              </div>
            </div>
          </div>
        </div>
        
        <div class="shortcut-footer">
          Press <kbd>?</kbd> or <kbd>Ctrl</kbd>+<kbd>/</kbd> to toggle this overlay · All shortcuts work inside the editor
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
defineEmits(['close'])

const shortcutGroups = [
  {
    title: 'File',
    shortcuts: [
      { label: 'Save Report', keys: ['Ctrl', 'S'] },
      { label: 'Preview Report', keys: ['Ctrl', 'P'] },
      { label: 'Command Palette', keys: ['Ctrl', 'K'] },
      { label: 'Share Report', keys: ['Ctrl', 'Shift', 'S'] },
    ]
  },
  {
    title: 'Edit',
    shortcuts: [
      { label: 'Undo', keys: ['Ctrl', 'Z'] },
      { label: 'Redo', keys: ['Ctrl', 'Y'] },
      { label: 'Cut', keys: ['Ctrl', 'X'] },
      { label: 'Copy', keys: ['Ctrl', 'C'] },
      { label: 'Paste', keys: ['Ctrl', 'V'] },
      { label: 'Duplicate', keys: ['Ctrl', 'D'] },
      { label: 'Delete', keys: ['Del'] },
      { label: 'Select All', keys: ['Ctrl', 'A'] },
      { label: 'Find & Replace', keys: ['Ctrl', 'F'] },
    ]
  },
  {
    title: 'View',
    shortcuts: [
      { label: 'Zoom In', keys: ['Ctrl', '+'] },
      { label: 'Zoom Out', keys: ['Ctrl', '-'] },
      { label: 'Reset Zoom', keys: ['Ctrl', '0'] },
      { label: 'Toggle Grid', keys: ['Ctrl', 'G'] },
      { label: 'Toggle Fullscreen', keys: ['F11'] },
      { label: 'Presentation Mode', keys: ['Ctrl', 'F5'] },
      { label: 'Measure Tool', keys: ['Ctrl', 'M'] },
    ]
  },
  {
    title: 'Elements',
    shortcuts: [
      { label: 'Move Element', keys: ['Arrow Keys'] },
      { label: 'Move 10px', keys: ['Shift', 'Arrow'] },
      { label: 'Deselect', keys: ['Esc'] },
      { label: 'Bold', keys: ['Ctrl', 'B'] },
      { label: 'Italic', keys: ['Ctrl', 'I'] },
      { label: 'Underline', keys: ['Ctrl', 'U'] },
    ]
  },
  {
    title: 'Pages',
    shortcuts: [
      { label: 'Add Page', keys: ['Ctrl', 'N'] },
      { label: 'Next Page', keys: ['PgDn'] },
      { label: 'Prev Page', keys: ['PgUp'] },
    ]
  },
]
</script>

<style scoped>
.shortcut-backdrop {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.6);
  display: flex; align-items: center; justify-content: center;
  z-index: 10000;
  backdrop-filter: blur(4px);
}

.shortcut-dialog {
  width: 680px; max-width: 90vw; max-height: 80vh;
  background: var(--bg-panel, #ffffff);
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
  overflow: hidden;
  display: flex; flex-direction: column;
}

.shortcut-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border, #e2e8f0);
}

.shortcut-header h2 {
  font-size: 16px; font-weight: 700;
  color: var(--text-primary, #0f172a);
  display: flex; align-items: center; gap: 8px;
}
.shortcut-header h2 i { color: var(--accent, #6366f1); }

.shortcut-close {
  width: 30px; height: 30px; border: none; background: transparent;
  border-radius: 8px; cursor: pointer; color: var(--text-muted, #94a3b8);
  font-size: 16px; display: flex; align-items: center; justify-content: center;
  transition: all 0.15s;
}
.shortcut-close:hover { background: var(--bg-secondary, #f8fafc); color: var(--text-primary, #0f172a); }

.shortcut-grid {
  flex: 1; overflow-y: auto; padding: 16px 20px;
  display: grid; grid-template-columns: 1fr 1fr; gap: 20px;
}

.shortcut-group-title {
  font-size: 11px; font-weight: 700; text-transform: uppercase;
  letter-spacing: 0.06em; color: var(--text-muted, #94a3b8); margin-bottom: 8px;
}

.shortcut-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: 6px 0; border-bottom: 1px solid var(--border-light, #f1f5f9);
}

.shortcut-label { font-size: 12px; color: var(--text-secondary, #475569); font-weight: 500; }

.shortcut-keys { display: flex; gap: 3px; }

.shortcut-keys kbd {
  font-size: 10px; font-weight: 600; padding: 3px 7px; border-radius: 4px;
  background: var(--bg-secondary, #f8fafc);
  border: 1px solid var(--border, #e2e8f0);
  color: var(--text-secondary, #475569);
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.shortcut-footer {
  padding: 12px 20px; border-top: 1px solid var(--border, #e2e8f0);
  font-size: 11px; color: var(--text-muted, #94a3b8); text-align: center;
}

.shortcut-footer kbd {
  font-size: 10px; font-weight: 600; padding: 2px 6px; border-radius: 3px;
  background: var(--bg-secondary, #f8fafc);
  border: 1px solid var(--border, #e2e8f0);
}

@media (max-width: 640px) { .shortcut-grid { grid-template-columns: 1fr; } }
</style>