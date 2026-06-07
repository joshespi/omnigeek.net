if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js'));
}

window.mdToolbar = function () {
    return {
        notify(ta) {
            ta.dispatchEvent(new Event('input', { bubbles: true }));
            ta.focus();
        },
        wrap(before, after, placeholder) {
            const ta = this.$refs.textarea;
            if (!ta) return;
            const start = ta.selectionStart;
            const end = ta.selectionEnd;
            const selected = ta.value.slice(start, end) || placeholder;
            ta.setRangeText(before + selected + after, start, end, 'select');
            this.notify(ta);
        },
        insertLink() {
            const ta = this.$refs.textarea;
            if (!ta) return;
            const start = ta.selectionStart;
            const end = ta.selectionEnd;
            const selected = ta.value.slice(start, end) || 'link text';
            ta.setRangeText('[' + selected + '](url)', start, end, 'end');
            this.notify(ta);
        },
        insertPrefix(prefix) {
            const ta = this.$refs.textarea;
            if (!ta) return;
            const start = ta.selectionStart;
            const lineStart = ta.value.lastIndexOf('\n', start - 1) + 1;
            ta.setRangeText(prefix, lineStart, lineStart, 'end');
            this.notify(ta);
        },
    };
};
