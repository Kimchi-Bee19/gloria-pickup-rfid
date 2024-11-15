<script>
    let columnWidths = [25, 25, 25, 25]; // Initial widths as percentages

    function resizeColumn(index, event) {
        const startX = event.clientX;
        const startWidth = columnWidths[index];

        function onMouseMove(e) {
            const deltaX = e.clientX - startX;
            const newWidth = Math.max(5, Math.min(100, startWidth + (deltaX / window.innerWidth) * 100));
            
            // Adjust columns based on the index being resized
            if (index === 0) { // First column
                columnWidths[0] = newWidth;
                columnWidths[1] = Math.max(0, 100 - newWidth - columnWidths[2] - columnWidths[3]);
            } else if (index === 1) { // Second column
                columnWidths[1] = newWidth;
                columnWidths[2] = Math.max(0, 100 - newWidth - columnWidths[0] - columnWidths[3]);
            } else if (index === 2) { // Third column
                columnWidths[2] = newWidth;
                columnWidths[3] = Math.max(0, 100 - newWidth - columnWidths[0] - columnWidths[1]);
            } else if (index === 3) { // Fourth column
                columnWidths[3] = newWidth;
                columnWidths[2] = Math.max(0, 100 - newWidth - columnWidths[0] - columnWidths[1]);
            }
        }

        function onMouseUp() {
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        }

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    }
</script>

<style>
    .containerr {
        display: flex;
        height: 100vh;
        border: 1px solid #ccc;
    }

    .columnn {
        overflow: auto;
        border-right: 1px solid #ccc;
        position: relative;
    }

    .columnn:last-child {
        border-right: none; /* Remove border on the last column */
    }

    .resizer {
        width: 10px;
        cursor: ew-resize;
        background-color: #ccc;
        position: absolute;
        top: 0;
        right: 0;
        height: 100%;
        z-index: 1;
    }
</style>

<div class="containerr">
    <div class="columnn" style="width: {columnWidths[0]}%; position: relative;">
        <div class="resizer" on:mousedown={event => resizeColumn(0, event)}></div>
        <p>Column 1</p>
        <p>Resize me!</p>
    </div>
    <div class="columnn" style="width: {columnWidths[1]}%; position: relative;">
        <div class="resizer" on:mousedown={event => resizeColumn(1, event)}></div>
        <p>Column 2</p>
        <p>Resize me!</p>
    </div>
    <div class="columnn" style="width: {columnWidths[2]}%; position: relative;">
        <div class="resizer" on:mousedown={event => resizeColumn(2, event)}></div>
        <p>Column 3</p>
        <p>Resize me!</p>
    </div>
    <div class="columnn" style="width: {columnWidths[3]}%; position: relative;">
        <div class="resizer" on:mousedown={event => resizeColumn(3, event)}></div>
        <p>Column 4</p>
        <p>Resize me!</p>
    </div>
</div>
