import './style.scss';

document.addEventListener("DOMContentLoaded", function () {
    // Select all instances of the map block
const iframeContainers = document.querySelectorAll(".mapog-embed");
iframeContainers.forEach(container => {
        const selectedMapUrl = container.getAttribute("data-selected-map");

        if (selectedMapUrl) {
            embedMap(container, selectedMapUrl);
        } else {
            container.innerHTML = '<p>No map selected.</p>';
        }
    });

    // Function to embed the map into the iframe container
    function embedMap(container, url) {
        container.innerHTML = `<iframe src="${url}" width="100%" height="500" style="border:none;"></iframe>`;
    }
});
