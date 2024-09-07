// Function to extract images from the primary Zillow listing
function extractListingImages() {
    const imageElements = document.querySelectorAll('.HollywoodTile__StyledTile-qk6uhh-0.kNkwcX img[data-src], .HollywoodTile__StyledTile-qk6uhh-0.kNkwcX img[src]');
    const images = [];
    imageElements.forEach(img => {
        const src = img.getAttribute('data-src') || img.getAttribute('src');
        if (src && !src.includes('logo') && !src.includes('map')) {  // Filter out logos and maps
            images.push(src);
        }
    });
    return images;
}

// Function to extract the address from the Zillow listing
function extractAddress() {
    const addressElement = document.querySelector('h2[data-test-id*=bdp-building-address]');
    if (addressElement) {
        return addressElement.textContent.trim();
    }
    return null;
}

// Listen for messages from the background script
chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
    if (request.action === "scrapeData") {
        const images = extractListingImages();
        const address = extractAddress();

        sendResponse({
            images: images,
            address: address
        });
    }
});
