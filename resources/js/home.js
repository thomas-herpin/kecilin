window.copyToClipboard = function(text) {
    navigator.clipboard.writeText(text);
    const copyText = document.getElementById('copy-text');
    const originalText = copyText.innerText;

    copyText.innerText = "Tersalin!";
    copyText.classList.add('text-fuchsia-400');

    setTimeout(() => {
        copyText.innerText = originalText;
        copyText.classList.remove('text-fuchsia-400');
    }, 2000);
}

window.downloadQR = function(btn) {
    const svg = document.querySelector('#qr-container svg');
    if (!svg) return;

    // Ambil slug dari attribute data-slug yang dipasang di tombol
    const slug = btn.getAttribute('data-slug') || 'qr-code';
    
    const svgData = new XMLSerializer().serializeToString(svg);
    const svgBlob = new Blob([svgData], {type: 'image/svg+xml;charset=utf-8'});
    const url = URL.createObjectURL(svgBlob);
    
    const downloadLink = document.createElement('a');
    downloadLink.href = url;
    downloadLink.download = `Kecilin-${slug}.svg`;
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}