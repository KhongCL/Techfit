function submitForm(event, formId, url) {
    event.preventDefault();
    const form = document.getElementById(formId);
    const formData = new FormData(form);

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function runCode() {
    const language = document.getElementById('language').value;
    const code = document.getElementById('code').value;
    const outputElement = document.getElementById('output');

    console.log('Language:', language);
    console.log('Code:', code);

    // Map the selected language to Judge0's language IDs
    const languageMap = {
        python: 71, // Python (3.8.1)
        javascript: 63, // JavaScript (Node.js 12.14.0)
        java: 62, // Java (OpenJDK 13.0.1)
        c: 50, // C (GCC 9.2.0)
        cpp: 54 // C++ (GCC 9.2.0)
    };

    const languageId = languageMap[language];
    console.log('Language ID:', languageId);

    // Send code to Judge0 API for execution
    fetch('https://judge0-ce.p.rapidapi.com/submissions', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'x-rapidapi-host': 'judge0-ce.p.rapidapi.com',
            'x-rapidapi-key': '16e45e0444msh74685cf48a7221ap1744f9jsncbd9c40bbc5b' // Replace with your RapidAPI key
        },
        body: JSON.stringify({
            source_code: code,
            language_id: languageId
        })
    })
    .then(response => response.json())
    .then(data => {
        const token = data.token;
        console.log('Token:', token);

        // Poll the Judge0 API for the result
        const interval = setInterval(() => {
            fetch(`https://judge0-ce.p.rapidapi.com/submissions/${token}`, {
                method: 'GET',
                headers: {
                    'x-rapidapi-host': 'judge0-ce.p.rapidapi.com',
                    'x-rapidapi-key': '16e45e0444msh74685cf48a7221ap1744f9jsncbd9c40bbc5b' // Replace with your RapidAPI key
                }
            })
            .then(response => response.json())
            .then(result => {
                console.log('Result:', result);
                if (result.status.id <= 2) {
                    // In Queue or Processing
                    outputElement.textContent = 'Processing...';
                } else {
                    clearInterval(interval);
                    outputElement.textContent = result.stdout || result.stderr || 'Error: ' + result.compile_output;
                }
            });
        }, 1000);
    })
    .catch(error => {
        console.error('Error:', error);
        outputElement.textContent = 'Error: ' + error.message;
    });
}