<?php
session_start(); 


function displayLoginMessage() {
    echo '<script>
        alert("You need to log in to access this page.");
    </script>';
    exit();
}


if (!isset($_SESSION['user_id'])) {
    displayLoginMessage(); 
}


if ($_SESSION['role'] !== 'Admin') {
    displayLoginMessage(); 
}


session_write_close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assessments - TechFit</title>
    <link rel="stylesheet" href="styles.css">
    <style>
       li {
            color: white;
        }

        :root {
                --primary-color: #007bff;
                --secondary-color: #1e1e1e;
                --accent-color: #0056b3;
                --text-color: #e0e0e0;
                --background-color: #121212;
                --border-color: #333;
                --hover-background-color: #333;
                --hover-text-color: #fff;
                --button-hover-color: #80bdff;
                --popup-background-color: #1a1a1a;
                --popup-border-color: #444;
                --danger-color: #dc3545;
                --danger-hover-color: #c82333;
                --success-color: #28a745;
                --success-hover-color: #218838;
            }

       
        body {
            font-family: Arial, sans-serif;
            color: var(--text-color);
            background-color: var(--background-color);
        }

        main {
            padding: 20px;
        }

       
        .header-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .button-group {
            display: flex;
            gap: 10px;
        }

        .search-sort-controls {
            display: flex;
            align-items: center;
            padding: 10px;
            flex-wrap: nowrap;
        }

        .search-sort-controls span {
            margin-right: 10px;
            white-space: nowrap;
        }

       
        #sortDropdown {
            margin-right: 10px;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            background-color: var(--secondary-color);
            color: var(--text-color);
            transition: border-color 0.3s ease, background-color 0.3s ease, color 0.3s ease;
        }

        #sortDropdown:hover {
            border-color: var(--primary-color);
        }

        #sortDropdown option {
            background-color: var(--background-color);
            color: var(--text-color);
            border-radius: 2px;
        }

       
        .search-container {
            position: relative;
            flex-grow: 1;
        }

        .search-field-container {
            position: relative;
        }

        #searchInput {
            margin-left: 10px;
            padding-right: 40px;
            flex-grow: 1;
            padding: 10px 10px 10px 40px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            background: url('images/search_icon.png') no-repeat 10px center;
            background-size: 20px;
            transition: border-color 0.3s ease;
            color: var(--text-color);
            background-color: var(--secondary-color);
        }

        #searchInput:hover {
            border-color: var(--primary-color);
        }

        #clearSearch {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            display: none;
        }

        #noMatchesPopup {
            display: none;
            position: absolute;
            top: calc(100% + 10px);
            left: 10px;
            background: var(--popup-background-color);
            color: var(--text-color);
            padding: 10px;
            border: 1px solid var(--popup-border-color);
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            transition: opacity 0.3s ease;
            z-index: 1000;
        }

       
        button {
            background-color: var(--primary-color);
            color: var(--text-color);
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
            border-radius: 5px;
            font-weight: bold;
        }

        button:hover {
            background-color: var(--button-hover-color);
            color: var(--hover-text-color);
        }

        button.danger {
            background-color: var(--danger-color);
        }

        button.danger:hover {
            background-color: var(--danger-hover-color);
        }

        button.success {
            background-color: var(--success-color);
        }

        button.success:hover {
            background-color: var(--success-hover-color);
        }

       
        #deleteSelected {
            background-color: var(--danger-color);
        }

        #deleteSelected:hover {
            background-color: var(--danger-hover-color);
        }

        .action-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            gap: 20px;
        }

        #restoreSelectedButton {
            background-color: var(--success-color);
            margin-right: 20px;
            flex-shrink: 0;
            margin-right: 0;
        }

        #restoreSelectedButton:hover {
            background-color: var(--success-hover-color);
        }

       
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed;
        }

        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: var(--secondary-color);
            cursor: pointer;
            position: relative;
            transition: background-color 0.3s ease;
            padding-right: 20px;
        }

        th[data-column]:hover {
            background-color: var(--hover-background-color);
            color: var(--hover-text-color);
        }

        tr:hover {
            background-color: var(--hover-background-color);
            color: var(--hover-text-color);
        }

       
        .resizer {
            display: inline-block;
            width: 5px;
            cursor: col-resize;
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            z-index: 1;
        }

       
        th:first-child, td:first-child {
            width: 50px;
        }

        th[data-column="description"], td[data-column="description"] {
            max-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            position: relative;
        }

        th[data-column="assessment_name"], td[data-column="assessment_name"] {
            max-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            position: relative;
        }

        th[data-column="actions"], td[data-column="actions"] {
            width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            position: relative;
        }

       
        td a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: bold;
            margin-right: 5px;
            transition: color 0.3s ease;
            display: inline-block;
        }

        td a:hover {
            color: var(--button-hover-color);
        }

        .action-separator {
            margin: 0 5px;
            color: var (--text-color);
            display: inline-block;
        }

        td a.deleteAssessment {
            color: var(--danger-color);
        }

        td a.deleteAssessment:hover {
            color: var(--danger-hover-color);
        }

       
        th[data-column]::after {
            content: '';
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            border: 5px solid transparent;
            display: none;
        }

        th[data-column].asc::after {
            display: inline-block;
            border-bottom-color: var(--text-color);
        }

        th[data-column].desc::after {
            display: inline-block;
            border-top-color: var(--text-color);
        }

        th[data-column]:hover.asc::after {
            border-bottom-color: transparent;
            border-top-color: var(--hover-text-color);
        }

        th[data-column]:hover.desc::after {
            border-top-color: transparent;
            border-bottom-color: var(--hover-text-color);
        }

       
        .tooltip {
            position: absolute;
            background: var(--popup-background-color);
            color: var(--text-color);
            padding: 5px;
            border-radius: 5px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 1000;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            opacity: 1;
            visibility: visible;
            pointer-events: none;
        }

       
        .editable {
            position: relative;
        }

        .editable:hover::after {
            content: 'Double-click to edit';
            position: absolute;
            background: var(--popup-background-color);
            color: var(--text-color);
            padding: 5px;
            border-radius: 5px;
            font-size: 12px;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            z-index: 1000;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            opacity: 1;
            visibility: visible;
            pointer-events: none;
        }

       
        .editable input {
           
            width: 100%;
            padding: 5px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            background-color: var(--secondary-color);
            color: var(--text-color);
            resize: vertical;
            box-sizing: border-box;
        }

       
        #deleted-assessments-tab {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--background-color);
            padding: 20px;
            border: 1px solid var(--border-color);
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-height: 80vh;
            overflow-y: auto;
            z-index: 1000;
            width: 90%;
            transition: opacity 0.3s ease;
        }

        #deleted-assessments-tab {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--background-color);
            padding: 20px;
            border: 1px solid var(--border-color);
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-height: 80vh;
            overflow-y: auto;
            z-index: 1000;
            width: 90%;
            transition: opacity 0.3s ease;
        }

        #deleted-assessments-tab.show {
            display: block;
            opacity: 1;
        }

        #deleted-assessments-tab .header-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        #deleted-assessments-tab .deleted-search-container {
            position: relative;
            margin-left: auto;
            flex-grow: 1;
            display: flex;
            justify-content: flex-end;
        }

        .deleted-search-container {
            flex-grow: 1;
            margin-left: 20px;
        }

        .search-field-container {
            position: relative;
        }

        #deletedSearchInput {
            padding: 10px 40px 10px 40px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            background: url('images/search_icon.png') no-repeat 10px center;
            background-size: 20px;
            transition: border-color 0.3s ease;
            color: var(--text-color);
            background-color: var(--secondary-color);
            box-sizing: border-box;
        }

        #deletedSearchInput:hover {
            border-color: var(--primary-color);
        }

        #deletedClearSearch {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            display: none;
        }

        #deletedNoMatchesPopup {
            display: none;
            position: absolute;
            top: calc(100% + 10px);
            left: 10px;
            background: var(--popup-background-color);
            color: var(--text-color);
            padding: 10px;
            border: 1px solid var(--popup-border-color);
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            transition: opacity 0.3s ease;
            z-index: 1000;
        }

       
        .assessment-close-button {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            color: var(--text-color);
            font-size: 24px;
            cursor: pointer;
            transition: color 0.3s ease, transform 0.3s ease;
            z-index: 1001;
        }

        .assessment-close-button:hover {
            color: var(--accent-color);
            transform: scale(1.1);
            background: none;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let lastChecked = null;

            document.getElementById('selectAll').addEventListener('click', function() {
                var checkboxes = document.querySelectorAll('.selectAssessment');
                for (var checkbox of checkboxes) {
                    checkbox.checked = this.checked;
                }
            });

            document.querySelectorAll('.selectAssessment').forEach(function(checkbox) {
                checkbox.addEventListener('click', function(event) {
                    if (!lastChecked) {
                        lastChecked = this;
                        return;
                    }

                    if (event.shiftKey) {
                        let checkboxes = Array.from(document.querySelectorAll('.selectAssessment'));
                        let start = checkboxes.indexOf(this);
                        let end = checkboxes.indexOf(lastChecked);

                        checkboxes.slice(Math.min(start, end), Math.max(start, end) + 1)
                            .forEach(checkbox => checkbox.checked = lastChecked.checked);
                    }

                    lastChecked = this;
                });
            });

            document.querySelectorAll('.deleteAssessment').forEach(function(element) {
                element.addEventListener('click', function(event) {
                    event.preventDefault();
                    var assessmentId = this.getAttribute('data-id');
                    if (confirm('Are you sure you want to delete this assessment?')) {
                        window.location.href = 'delete_assessment.php?assessment_id=' + assessmentId;
                    }
                });
            });

            document.getElementById('deleteSelected').addEventListener('click', function() {
                var selected = [];
                document.querySelectorAll('.selectAssessment:checked').forEach(function(checkbox) {
                    selected.push(checkbox.value);
                });
                if (selected.length > 0) {
                    if (confirm('Are you sure you want to delete the selected assessments?')) {
                        window.location.href = 'delete_assessment.php?assessment_ids=' + selected.join(',');
                    }
                } else {
                    alert('Please select at least one assessment to delete.');
                }
            });

            document.querySelector('#deleted-assessments-tab .assessment-close-button').addEventListener('click', function() {
                closeDeletedAssessments();
            });

            document.getElementById('viewDeleted').addEventListener('click', function() {
                fetch('get_deleted_assessments.php')
                    .then(response => response.json())
                    .then(data => {
                        const deletedAssessmentsDiv = document.getElementById('deleted-assessments');
                        if (data.length > 0) {
                            deletedAssessmentsDiv.innerHTML = data.map(assessment => `
                                <tr>
                                    <td><input type="checkbox" class="selectDeletedAssessment" name="restore_assessments[]" value="${assessment.assessment_id}"></td>
                                    <td>${assessment.assessment_id}</td>
                                    <td>${assessment.assessment_name}</td>
                                    <td>${assessment.description}</td>
                                    <td>${assessment.last_modified}</td>
                                    <td>${assessment.timestamp}</td>
                                </tr>
                            `).join('');
                        } else {
                            deletedAssessmentsDiv.innerHTML = '<tr><td colspan="6">No deleted assessments found</td></tr>';
                        }
                        document.getElementById('deleted-assessments-tab').style.display = 'block';

                        
                        document.getElementById('select-all-deleted').addEventListener('change', function() {
                            const checkboxes = document.querySelectorAll('input[name="restore_assessments[]"]');
                            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
                        });

                        
                        let lastDeletedChecked = null;
                        document.querySelectorAll('.selectDeletedAssessment').forEach(function(checkbox) {
                            checkbox.addEventListener('click', function(event) {
                                if (!lastDeletedChecked) {
                                    lastDeletedChecked = this;
                                    return;
                                }

                                if (event.shiftKey) {
                                    let checkboxes = Array.from(document.querySelectorAll('.selectDeletedAssessment'));
                                    let start = checkboxes.indexOf(this);
                                    let end = checkboxes.indexOf(lastDeletedChecked);

                                    checkboxes.slice(Math.min(start, end), Math.max(start, end) + 1)
                                        .forEach(checkbox => checkbox.checked = lastDeletedChecked.checked);
                                }

                                lastDeletedChecked = this;
                            });
                        });

                        
                        document.getElementById('restoreSelectedButton').addEventListener('click', restoreSelectedAssessments);

                        
                        document.querySelectorAll('#deleted-assessments-tab th[data-column]').forEach(th => {
                            th.addEventListener('click', function() {
                                const column = this.getAttribute('data-column');
                                const currentOrder = this.dataset.order || -1;
                                const order = this.dataset.order = currentOrder * -1; 
                                console.log(`Sorting deleted assessments table column: ${column}, Order: ${order}`); 
                                const rows = Array.from(document.querySelectorAll('#deleted-assessments tr'));
                                rows.sort((a, b) => {
                                    const aText = a.querySelector(`td:nth-child(${this.cellIndex + 1})`).textContent.trim();
                                    const bText = b.querySelector(`td:nth-child(${this.cellIndex + 1})`).textContent.trim();
                                    return aText.localeCompare(bText, undefined, {numeric: true}) * order;
                                });
                                rows.forEach(row => document.querySelector('#deleted-assessments').appendChild(row));

                                
                                document.querySelectorAll('#deleted-assessments-tab th[data-column]').forEach(th => th.classList.remove('asc', 'desc'));
                                this.classList.add(order === 1 ? 'asc' : 'desc');
                            });
                        });
                    });
            });

            
            document.querySelectorAll('th[data-column]').forEach(th => {
                th.addEventListener('mouseenter', function(event) {
                    const tooltip = document.createElement('div');
                    tooltip.className = 'tooltip';
                    tooltip.textContent = 'Click to sort';
                    document.body.appendChild(tooltip);
                    const rect = th.getBoundingClientRect();
                    tooltip.style.top = `${rect.bottom + window.scrollY}px`; 
                    tooltip.style.left = `${rect.left + window.scrollX}px`; 
                    th._tooltip = tooltip; 
                });

                th.addEventListener('mouseleave', function() {
                    if (th._tooltip) {
                        th._tooltip.remove();
                        th._tooltip = null;
                    }
                });

                th.addEventListener('click', function() {
                    if (this.closest('#deleted-assessments-tab')) {
                        return; 
                    }
                    const column = this.getAttribute('data-column');
                    const currentOrder = this.dataset.order || -1;
                    const order = this.dataset.order = currentOrder * -1; 
                    console.log(`Sorting main table column: ${column}, Order: ${order}`); 
                    const rows = Array.from(document.querySelectorAll('#assessmentsTableBody tr'));
                    rows.sort((a, b) => {
                        const aText = a.querySelector(`td:nth-child(${this.cellIndex + 1})`).textContent.trim();
                        const bText = b.querySelector(`td:nth-child(${this.cellIndex + 1})`).textContent.trim();
                        return aText.localeCompare(bText, undefined, {numeric: true}) * order;
                    });
                    rows.forEach(row => document.querySelector('#assessmentsTableBody').appendChild(row));

                    
                    document.querySelectorAll('th[data-column]').forEach(th => th.classList.remove('asc', 'desc'));
                    this.classList.add(order === 1 ? 'asc' : 'desc');
                });
            });

            document.getElementById('deletedSearchInput').addEventListener('input', function() {
                const filter = this.value.toLowerCase();
                const rows = document.querySelectorAll('#deleted-assessments tr');
                let matchFound = false;
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    const match = Array.from(cells).some(cell => cell.textContent.toLowerCase().includes(filter));
                    row.style.display = match ? '' : 'none';
                    if (match) matchFound = true;
                });
                const noMatchesPopup = document.getElementById('deletedNoMatchesPopup');
                if (!matchFound) {
                    noMatchesPopup.style.display = 'block';
                    noMatchesPopup.style.opacity = '1';
                } else {
                    noMatchesPopup.style.display = 'none';
                }
                document.getElementById('deletedClearSearch').style.display = filter ? 'block' : 'none';
            });

            document.getElementById('deletedClearSearch').addEventListener('click', function() {
                document.getElementById('deletedSearchInput').value = '';
                const rows = document.querySelectorAll('#deleted-assessments tr');
                rows.forEach(row => {
                    row.style.display = '';
                });
                this.style.display = 'none';
                document.getElementById('deletedNoMatchesPopup').style.display = 'none';
            });

            document.getElementById('deletedSearchInput').addEventListener('focus', function() {
                const noMatchesPopup = document.getElementById('deletedNoMatchesPopup');
                if (this.value && !Array.from(document.querySelectorAll('#deleted-assessments tr')).some(row => row.style.display !== 'none')) {
                    noMatchesPopup.style.display = 'block';
                    noMatchesPopup.style.opacity = '1';
                }
            });

            document.addEventListener('click', function(event) {
                const noMatchesPopup = document.getElementById('deletedNoMatchesPopup');
                if (!document.getElementById('deletedSearchInput').contains(event.target) && !noMatchesPopup.contains(event.target)) {
                    noMatchesPopup.style.display = 'none';
                }
            });

            function closeDeletedAssessments() {
                const tab = document.getElementById('deleted-assessments-tab');
                if (tab) {
                    tab.style.display = 'none';
                }
                
                document.querySelectorAll('.tooltip').forEach(tooltip => tooltip.remove());
            }

            function restoreSelectedAssessments() {
                const selected = document.querySelectorAll('input[name="restore_assessments[]"]:checked');
                if (selected.length === 0) {
                    alert('Please select at least one assessment to restore.');
                    return;
                }

                if (confirm('Are you sure you want to restore the selected assessments?')) {
                    const form = document.getElementById('restore-form');
                    const formData = new FormData(form);

                    fetch('restore_assessments.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Selected assessments restored successfully.');
                            location.reload(); 
                        } else {
                            alert('Failed to restore selected assessments.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while restoring the assessments.');
                    });
                }
            }

            document.getElementById('searchInput').addEventListener('input', function() {
                const filter = this.value.toLowerCase();
                const rows = document.querySelectorAll('#assessmentsTableBody tr');
                let matchFound = false;
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    const match = Array.from(cells).some(cell => cell.textContent.toLowerCase().includes(filter));
                    row.style.display = match ? '' : 'none';
                    if (match) matchFound = true;
                });
                const noMatchesPopup = document.getElementById('noMatchesPopup');
                if (!matchFound) {
                    noMatchesPopup.style.display = 'block';
                    noMatchesPopup.style.opacity = '1';
                } else {
                    noMatchesPopup.style.display = 'none';
                }
                document.getElementById('clearSearch').style.display = filter ? 'block' : 'none';
            });

            document.getElementById('clearSearch').addEventListener('click', function() {
                document.getElementById('searchInput').value = '';
                const rows = document.querySelectorAll('#assessmentsTableBody tr');
                rows.forEach(row => {
                    row.style.display = '';
                });
                this.style.display = 'none';
                document.getElementById('noMatchesPopup').style.display = 'none';
            });

            document.getElementById('searchInput').addEventListener('focus', function() {
                const noMatchesPopup = document.getElementById('noMatchesPopup');
                if (this.value && !Array.from(document.querySelectorAll('#assessmentsTableBody tr')).some(row => row.style.display !== 'none')) {
                    noMatchesPopup.style.display = 'block';
                    noMatchesPopup.style.opacity = '1';
                }
            });

            document.addEventListener('click', function(event) {
                const noMatchesPopup = document.getElementById('noMatchesPopup');
                if (!document.getElementById('searchInput').contains(event.target) && !noMatchesPopup.contains(event.target)) {
                    noMatchesPopup.style.display = 'none';
                }
            });

            document.getElementById('sortDropdown').addEventListener('change', function() {
                const value = this.value;
                const rows = Array.from(document.querySelectorAll('#assessmentsTableBody tr'));
                let columnIndex, order;

                switch (value) {
                    case 'assessment_id_asc':
                        columnIndex = 1;
                        order = 1;
                        break;
                    case 'assessment_id_desc':
                        columnIndex = 1;
                        order = -1;
                        break;
                    case 'admin_id_asc':
                        columnIndex = 2;
                        order = 1;
                        break;
                    case 'admin_id_desc':
                        columnIndex = 2;
                        order = -1;
                        break;
                    case 'last_modified_asc':
                        columnIndex = 6;
                        order = 1;
                        break;
                    case 'last_modified_desc':
                        columnIndex = 6;
                        order = -1;
                        break;
                    default:
                        return;
                }

                rows.sort((a, b) => {
                    const aText = a.querySelector(`td:nth-child(${columnIndex + 1})`).textContent.trim();
                    const bText = b.querySelector(`td:nth-child(${columnIndex + 1})`).textContent.trim();
                    return aText.localeCompare(bText, undefined, {numeric: true}) * order;
                });

                rows.forEach(row => document.querySelector('#assessmentsTableBody').appendChild(row));
            });

            document.querySelectorAll('th[data-column]').forEach(th => {
                th.addEventListener('click', function() {
                    if (this.closest('#deleted-assessments-tab')) {
                        return; 
                    }
                    const column = this.getAttribute('data-column');
                    const currentOrder = this.dataset.order || -1;
                    const order = this.dataset.order = currentOrder * -1; 
                    console.log(`Sorting main table column: ${column}, Order: ${order}`); 
                    const rows = Array.from(document.querySelectorAll('#assessmentsTableBody tr'));
                    rows.sort((a, b) => {
                        const aText = a.querySelector(`td:nth-child(${this.cellIndex + 1})`).textContent.trim();
                        const bText = b.querySelector(`td:nth-child(${this.cellIndex + 1})`).textContent.trim();
                        return aText.localeCompare(bText, undefined, {numeric: true}) * order;
                    });
                    rows.forEach(row => document.querySelector('#assessmentsTableBody').appendChild(row));

                    
                    document.querySelectorAll('th[data-column]').forEach(th => th.classList.remove('asc', 'desc'));
                    this.classList.add(order === 1 ? 'asc' : 'desc');
                }, { once: true }); 
            });

            
            document.querySelectorAll('.editable').forEach(cell => {
                cell.addEventListener('mouseenter', function(event) {
                    const tooltip = document.createElement('div');
                    tooltip.className = 'tooltip';
                    tooltip.textContent = 'Double-click to edit';
                    document.body.appendChild(tooltip);
                    const rect = cell.getBoundingClientRect();
                    tooltip.style.top = `${rect.bottom + window.scrollY}px`; 
                    tooltip.style.left = `${rect.left + window.scrollX}px`; 
                    cell._tooltip = tooltip; 
                });

                cell.addEventListener('mouseleave', function() {
                    if (cell._tooltip) {
                        cell._tooltip.remove();
                        cell._tooltip = null;
                    }
                });

                cell.addEventListener('dblclick', function() {
                    const originalText = this.textContent;
                    const input = document.createElement('textarea'); 
                    input.value = originalText;
                    input.style.width = '100%';
                    this.textContent = '';
                    this.appendChild(input);
                    input.focus();

                    input.addEventListener('blur', () => {
                        this.textContent = originalText;
                    });

                    input.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter') {
                            const newValue = input.value;
                            const assessmentId = this.getAttribute('data-id');
                            const column = this.getAttribute('data-column');

                            fetch('update_assessment.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    assessment_id: assessmentId,
                                    column: column,
                                    value: newValue
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    this.textContent = newValue;
                                } else {
                                    this.textContent = originalText;
                                    alert('Failed to update assessment.');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                this.textContent = originalText;
                                alert('An error occurred while updating the assessment.');
                            });
                        }
                    });
                });
            });
        });

            
            document.querySelectorAll('th').forEach(th => {
                const resizer = document.createElement('div');
                resizer.classList.add('resizer');
                th.appendChild(resizer);
                resizer.addEventListener('mousedown', initResize);
            });

            let startX, startWidth, resizer;

            function initResize(e) {
                startX = e.clientX;
                resizer = e.target;
                startWidth = resizer.parentElement.offsetWidth;
                document.addEventListener('mousemove', resizeColumn);
                document.addEventListener('mouseup', stopResize);
            }

            function resizeColumn(e) {
                const newWidth = startWidth + (e.clientX - startX);
                const maxWidth = 500; 
                const minWidth = 50; 
                if (newWidth > maxWidth) {
                    resizer.parentElement.style.width = maxWidth + 'px';
                    resizer.parentElement.style.minWidth = maxWidth + 'px';
                    resizer.parentElement.style.maxWidth = maxWidth + 'px';
                } else if (newWidth < minWidth) {
                    resizer.parentElement.style.width = minWidth + 'px';
                    resizer.parentElement.style.minWidth = minWidth + 'px';
                    resizer.parentElement.style.maxWidth = minWidth + 'px';
                } else {
                    resizer.parentElement.style.width = newWidth + 'px';
                    resizer.parentElement.style.minWidth = newWidth + 'px';
                    resizer.parentElement.style.maxWidth = newWidth + 'px';
                }
            }

            function stopResize() {
                document.removeEventListener('mousemove', resizeColumn);
                document.removeEventListener('mouseup', stopResize);
            }
    </script>
</head>
<body>
<header>
        <div class="logo">
            <a href="index.php"><img src="images/logo.jpg" alt="TechFit Logo"></a>
        </div>
        <nav>
            <div class="nav-container">
                <div class="hamburger" id="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <ul class="nav-list">
                    <li><a href="#">Assessments</a>
                        <ul class="dropdown">
                            <li><a href="create_assessment.php">Create New Assessment</a></li>
                            <li><a href="manage_assessments.php">Manage Assessments</a></li>
                            <li><a href="view_assessment_results.php">View Assessment Results</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Users</a>
                        <ul class="dropdown">
                            <li><a href="manage_users.php">Manage Users</a></li>
                            <li><a href="user_feedback.php">User Feedback</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Reports</a>
                        <ul class="dropdown">
                            <li><a href="assessment_performance.php">Assessment Performance</a></li>
                       
                        </ul>
                    </li>
                    <li><a href="#">Resources</a>
                        <ul class="dropdown">
                            <li><a href="useful_links.php">Manage Useful Links</a></li>
                            <li><a href="faq.php">Manage FAQs</a></li>
                            <li><a href="sitemap.php">Manage Sitemap</a></li>
                        </ul>
                    </li>
                    <li><a href="about.php">About</a></li>
                    <li>
                        <a href="#" id="profile-link">
                            <div class="profile-info">
                                <span class="username" id="username">
                                    <?php
                                    
                                    if (isset($_SESSION['username'])) {
                                        echo $_SESSION['username'];  
                                    } else {
                                        echo "Guest";  
                                    }
                                    ?>
                                </span>
                                <img src="images/usericon.png" alt="Profile" class="profile-image" id="profile-image">
                            </div>
                        </a>
                        <ul class="dropdown" id="profile-dropdown">
                        <li><a>Settings</a>
                                <ul class="dropdown">
                                    <li><a href="manage_profile.php">Manage Profile</a></li>
                                    <li><a href="system_configuration.php">System Configuration Settings</a></li>
                                </ul>
                            </li>
                            <li><a href="#" >Logout</a></li>
                        </ul>
                    </li>                    
                </ul>
            </div>
        </nav>
    </header>    
    <div id="logout-popup" class="popup">
        <h2>Are you sure you want to Log Out?</h2>
        <button class="close-button" id="logout-confirm-button">Yes</button>
        <button class="cancel-button" id="logout-cancel-button">No</button>
    </div>
        <main>
        <h1>Manage Assessments</h1>
        <div class="header-controls">
            <div class="button-group">
                <button onclick="window.location.href='create_assessment.php'">Create New Assessment</button>
                <button id="deleteSelected" class="danger">Delete Selected Assessment</button>
                <button id="viewDeleted">View Deleted Assessments</button>
            </div>
            <div class="search-sort-controls">
                <span>Sort by key:</span>
                <select id="sortDropdown">
                    <option value="none">None</option>
                    <option value="assessment_id_asc">Assessment ID ASC</option>
                    <option value="assessment_id_desc">Assessment ID DESC</option>
                    <option value="admin_id_asc">Admin ID ASC</option>
                    <option value="admin_id_desc">Admin ID DESC</option>
                </select>
                <div class="search-container">
                    <div class="search-field-container">
                        <input type="text" id="searchInput" placeholder="Search...">
                        <span id="clearSearch">&#x2715;</span>
                        <div id="noMatchesPopup">No matches found.</div>
                    </div>
                </div>
            </div>
        </div>
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th data-column="assessment_id">Assessment ID</th>
                        <th data-column="admin_id">Admin ID</th>
                        <th data-column="assessment_name">Assessment Name</th>
                        <th data-column="description">Description</th>
                        <th data-column="last_modified">Last Modified</th>
                        <th data-column="timestamp">Timestamp</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="assessmentsTableBody">
                    <?php
                    $servername = "localhost";
                    $username = "root";
                    $password = "";
                    $dbname = "techfit";

                    
                    $conn = new mysqli($servername, $username, $password, $dbname);

                    
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    $sql = "SELECT assessment_id, admin_id, assessment_name, description, timestamp, last_modified FROM Assessment_Admin WHERE is_active = 1";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><input type='checkbox' class='selectAssessment' value='" . htmlspecialchars($row['assessment_id']) . "'></td>";
                            echo "<td>" . htmlspecialchars($row['assessment_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['admin_id']) . "</td>";
                            echo "<td class='editable' data-id='" . htmlspecialchars($row['assessment_id']) . "' data-column='assessment_name'>" . htmlspecialchars($row['assessment_name']) . "</td>";
                            echo "<td class='editable' data-id='" . htmlspecialchars($row['assessment_id']) . "' data-column='description'>" . htmlspecialchars($row['description']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['last_modified']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['timestamp']) . "</td>";
                            echo "<td><a href='edit_assessment.php?assessment_id=" . htmlspecialchars($row['assessment_id']) . "'>Edit</a> <span class='action-separator'>|</span> <a href='#' class='deleteAssessment' data-id='" . htmlspecialchars($row['assessment_id']) . "'>Delete</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8'>No assessments found</td></tr>";
                    }

                    $conn->close();
                    ?>
                </tbody>
            </table>
            <div id="deleted-assessments-tab" style="display:none;">
                <div class="header-controls">
                    <h3>Deleted Assessments</h3>
                </div>
                <div class="action-controls">
                    <button type="button" class="success" id="restoreSelectedButton" onclick="restoreSelectedAssessments()">
                        Restore Selected Assessments
                    </button>
                    <div class="deleted-search-container">
                        <div class="search-field-container">
                            <input type="text" id="deletedSearchInput" placeholder="Search...">
                            <span id="deletedClearSearch">&#x2715;</span>
                            <div id="deletedNoMatchesPopup">No matches found.</div>
                        </div>
                    </div>
                </div>
                <form id="restore-form">
                    <table>
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all-deleted"></th>
                                <th data-column="assessment_id">Assessment ID</th>
                                <th data-column="assessment_name">Assessment Name</th>
                                <th data-column="description">Description</th>
                                <th data-column="last_modified">Last Modified</th>
                                <th data-column="timestamp">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody id="deleted-assessments"></tbody>
                    </table>
                </form>
                <button type="button" class="assessment-close-button" onclick="closeDeletedAssessments()">&#x2715;</button>
            </div>
        </main>
        <footer>
        <div class="footer-content">
            <div class="footer-left">
                <div class="footer-logo">
                    <a href="index.php"><img src="images/logo.jpg" alt="TechFit Logo"></a>
                </div>
                <div class="social-media">
                    <p>Keep up with TechFit:</p>
                    <div class="social-icons">
                        <a href="https://facebook.com"><img src="images/facebook.png" alt="Facebook"></a>
                        <a href="https://twitter.com"><img src="images/twitter.png" alt="Twitter"></a>
                        <a href="https://instagram.com"><img src="images/instagram.png" alt="Instagram"></a>
                        <a href="https://linkedin.com"><img src="images/linkedin.png" alt="LinkedIn"></a>
                    </div>
                    <p><a href="mailto:techfit@gmail.com">techfit@gmail.com</a></p>
                </div>
            </div>
            <div class="footer-right">
                <div class="footer-column">
                    <h3>Assessments</h3>
                    <ul>
                        <li><a href="create_assessment.php">Create New Assessment</a></li>
                        <li><a href="manage_assessments.php">Manage Assessments</a></li>
                        <li><a href="view_assessment_results.php">View Assessment Results</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Users</h3>
                    <ul>
                        <li><a href="manage_users.php">Manage Users</a></li>
                        <li><a href="user_feedback.php">User Feedback</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Reports</h3>
                    <ul>
                        <li><a href="assessment_performance.php">Assessment Performance</a></li>
                        
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="useful_links.php">Manage Useful Links</a></li>
                        <li><a href="faq.php">Manage FAQs</a></li>
                        <li><a href="sitemap.php">Manage Sitemap</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>About</h3>
                    <ul>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="terms.php">Terms of Service</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 TechPathway: TechFit. All rights reserved.</p>
        </div>
    </footer>
    <script src = "scripts.js"></script>
</body>
</html>