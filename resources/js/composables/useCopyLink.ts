import { ref } from 'vue';

export function useCopyLink() {
    const copied = ref(false);

    function copyLink() {
        const url = window.location.href;

        const markCopied = () => {
            copied.value = true;
            setTimeout(() => (copied.value = false), 2000);
        };

        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(markCopied);
            return;
        }

        const textarea = document.createElement('textarea');
        textarea.value = url;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        markCopied();
    }

    return { copied, copyLink };
}
