function filterJobs() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
    const selectedCategories = Array.from(checkboxes).map(cb => cb.value);
    const cards = document.querySelectorAll('[data-category]');

    if (selectedCategories.length === 0) {
        // No filters selected, show all cards
        cards.forEach(card => {
            card.style.display = 'block';
        });
    } else {
        // Show matching cards, hide non-matching ones
        cards.forEach(card => {
            const category = card.getAttribute('data-category');
            card.style.display = selectedCategories.includes(category) ? 'block' : 'none';
        });
    }
}

// Clear filters
function clearFilters() {
    document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        cb.checked = false;
    });
    // Show all cards
    document.querySelectorAll('[data-category]').forEach(card => {
        card.style.display = 'block';
    });
}
// Initialize on page load
// init();
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('show');
}

document.addEventListener('click', function(event) {
    const container = document.querySelector('.user-menu-container');
    if (!container.contains(event.target)) {
        document.getElementById('userDropdown').classList.remove('show');
    }
});
