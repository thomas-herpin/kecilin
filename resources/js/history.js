window.copyHistoryLink = function(btn, url) {
    navigator.clipboard.writeText(url);
    const label = btn.querySelector('.label');
    const originalText = label.innerText;
    
    label.innerText = "Tersalin!";
    btn.classList.replace('bg-violet-50', 'bg-fuchsia-50');
    btn.classList.replace('text-violet-600', 'text-fuchsia-600');

    setTimeout(() => {
        label.innerText = originalText;
        btn.classList.replace('bg-fuchsia-50', 'bg-violet-50');
        btn.classList.replace('text-fuchsia-600', 'text-violet-600');
    }, 2000);
}

window.downloadHistoryQR = function(id, slug) {
    const qrContainer = document.getElementById('qr-' + id);
    const svg = qrContainer.querySelector('svg');
    if (!svg) return;

    const svgData = new XMLSerializer().serializeToString(svg);
    const svgBlob = new Blob([svgData], {type: 'image/svg+xml;charset=utf-8'});
    const url = URL.createObjectURL(svgBlob);
    
    const downloadLink = document.createElement('a');
    downloadLink.href = url;
    downloadLink.download = `Kecilin-${slug}.svg`;
    downloadLink.click();
}