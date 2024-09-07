chrome.runtime.onInstalled.addListener(() => {
    console.log('Zillow Listing Scraper installed.');
});

// Listen for messages from the popup
chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
    if (request.action === "scrape") {
        chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
            chrome.tabs.sendMessage(tabs[0].id, { action: "scrapeData" }, (response) => {
                if (chrome.runtime.lastError) {
                    console.error(chrome.runtime.lastError);
                } else {
                    sendResponse(response);
                }
            });
        });
        return true;  // Indicates async response
    }
});
