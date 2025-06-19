export function makeDraggable(el) {
    let isDragging = false, offsetX, offsetY;

    el.addEventListener('mousedown', function (e) {
        isDragging = true;
        offsetX = e.offsetX;
        offsetY = e.offsetY;
        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    });

    function onMouseMove(e) {
        if (!isDragging) return;
        const canvas = document.getElementById('map-canvas');
        const canvasRect = canvas.getBoundingClientRect();
        el.style.left = (e.clientX - canvasRect.left - offsetX) + 'px';
        el.style.top = (e.clientY - canvasRect.top - offsetY) + 'px';
    }

    function onMouseUp() {
        isDragging = false;
        document.removeEventListener('mousemove', onMouseMove);
        document.removeEventListener('mouseup', onMouseUp);
    }
}
