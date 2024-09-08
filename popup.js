document.getElementById('scrapeButton').addEventListener('click', () => {
    chrome.runtime.sendMessage({ action: "scrape" }, (response) => {
        if (response) {
            displayScrapedData(response);
            // Show the "Send to WordPress" button
            document.getElementById('sendToWordPressButton').style.display = 'block';
            // Store the data for later use
            localStorage.setItem('scrapedData', JSON.stringify(response));
        } else {
            alert('Failed to scrape data. Please ensure you are on a Zillow listing page.');
        }
    });
});

function displayScrapedData(data) {
    const output = document.getElementById('output');
    let html = '<h2>Scraped Data:</h2>';

    // Display address
    html += `<h3>Address:</h3><p>${data.address}</p>`;

    // Display all other data fields
    for (const [key, value] of Object.entries(data)) {
        if (key !== 'address' && key !== 'images') {
            html += `<h3>${key.charAt(0).toUpperCase() + key.slice(1)}:</h3>`;
            html += Array.isArray(value) ? `<ul>${value.map(item => `<li>${item}</li>`).join('')}</ul>` : `<p>${value}</p>`;
        }
    }

    // Display images
    html += '<h3>Images:</h3><div id="images"></div>';

    output.innerHTML = html;

    // Add images to the container
    const imagesContainer = document.getElementById('images');
    data.images.forEach(src => {
        const img = document.createElement('img');
        img.src = src;
        img.style.width = '100px';
        img.style.height = '100px';
        img.style.margin = '5px';
        imagesContainer.appendChild(img);
    });
}

document.getElementById('sendToWordPressButton').addEventListener('click', () => {
    const scrapedData = JSON.parse(localStorage.getItem('scrapedData'));
    console.log(scrapedData);
    if (scrapedData) {
        fetch('http://3.209.237.120/wp-json/zls/v1/submit-listing', {
            method: 'POST',
            mode: 'no-cors', // Added this line
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(scrapedData)
        })
        .then(response => {
            // Since 'no-cors' mode doesn't allow reading the response, we won't handle the response here.
            alert('Listing sent to WordPress successfully!');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while sending the listing to WordPress.');
        });
    } else {
        alert('No scraped data found. Please scrape data first.');
    }
});

