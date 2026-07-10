export function downloadQrAsPng(svgUrl: string, filename: string = 'ticket.png') {
    const img = new Image();
    img.crossOrigin = 'Anonymous';
    img.onload = () => {
        const canvas = document.createElement('canvas');
        // Use a high resolution for crisp QR codes
        canvas.width = 1000;
        canvas.height = 1000;
        
        const ctx = canvas.getContext('2d');
        if (!ctx) return;
        
        // Fill white background (SVGs are transparent)
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        // Draw the image with some padding
        const padding = 50;
        ctx.drawImage(img, padding, padding, canvas.width - padding * 2, canvas.height - padding * 2);
        
        // Convert to PNG and download
        const pngUrl = canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.href = pngUrl;
        link.download = filename;
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };
    
    // Trigger load
    img.src = svgUrl;
}
