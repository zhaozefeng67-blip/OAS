// Filter function
function filterPrograms() {
    const selectedCategories = Array.from(
        document.querySelectorAll('.category-filter:checked')
    ).map(cb => cb.value);
    
    const selectedDegrees = Array.from(
        document.querySelectorAll('.degree-filter:checked')
    ).map(cb => cb.value.toLowerCase()); // Normalize to lowercase for comparison
    
    const searchText = document.getElementById('search-input').value.toLowerCase();
    const allCards = document.querySelectorAll('.program-card');
    let visibleCount = 0;
    
    allCards.forEach(card => {
        const cardCategory = card.getAttribute('data-category');
        const cardDegreeType = (card.getAttribute('data-degree-type') || '').toLowerCase();
        const cardName = card.getAttribute('data-name').toLowerCase();
        
        const categoryMatch = selectedCategories.length === 0 || 
                             selectedCategories.includes(cardCategory);
        
        // Match degree type (handle case variations: Master/master, MBA/mba, PhD/phd)
        const degreeMatch = selectedDegrees.length === 0 || 
                           selectedDegrees.some(selected => {
                               const normalizedSelected = selected.toLowerCase();
                               const normalizedCard = cardDegreeType.toLowerCase();
                               return normalizedCard === normalizedSelected;
                           });
        
        const searchMatch = searchText === '' || cardName.includes(searchText);
        
        if (categoryMatch && degreeMatch && searchMatch) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    document.getElementById('program-count').textContent = visibleCount;
}

// Clear all filters
function clearFilters() {
    document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
    document.getElementById('search-input').value = '';
    filterPrograms();
}

// Initialize after page load
document.addEventListener('DOMContentLoaded', function() {
    filterPrograms();
    
    // checkbox events
    document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', filterPrograms);
    });
    
    // Filter group expand/collapse
    document.querySelectorAll('.filter-title').forEach(title => {
        title.addEventListener('click', function() {
            this.classList.toggle('active');
            const options = this.nextElementSibling;
            options.style.display = options.style.display === 'none' ? 'flex' : 'none';
        });
    });
    
    // Apply button for each card
    document.querySelectorAll('.program-card').forEach(card => {
        const applyBtn = card.querySelector('.apply-btn');
        const popconfirm = card.querySelector('.popconfirm');
        const noBtn = card.querySelector('.pop-no');
        const yesBtn = card.querySelector('.pop-yes');
        
        // Click Apply to show popup
        applyBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            // Close other popups
            document.querySelectorAll('.popconfirm').forEach(p => p.style.display = 'none');
            popconfirm.style.display = 'block';
        });
        
        // Click No
        noBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            popconfirm.style.display = 'none';
        });
        
        // Click Yes - submit form
        yesBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            // Fill form data
            document.getElementById('form-pid').value = card.dataset.pid;
            document.getElementById('form-pname').value = card.dataset.name;
            // Submit form
            document.getElementById('apply-form').submit();
        });
    });
    
    // Click elsewhere to close popup
    document.addEventListener('click', function() {
        document.querySelectorAll('.popconfirm').forEach(p => p.style.display = 'none');
    });
});