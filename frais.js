document.addEventListener('DOMContentLoaded', function() {
    const monthBtn = document.getElementById('current-month');
    const monthList = document.querySelector('.month-list');

    if (monthBtn && monthList) {
        setupMonthDropdown(monthBtn, monthList);
        generateMonthList(monthList, monthBtn);
    }
});

function setupMonthDropdown(monthBtn, monthList) {
    monthBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        monthList.classList.toggle('active');
    });

    monthList.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    document.addEventListener('click', function() {
        monthList.classList.remove('active');
    });
}

function generateMonthList(monthList, monthBtn) {
    const today = new Date();
    const selectedValue = monthBtn.dataset.month || today.toISOString().slice(0, 7);
    const months = [];

    for (let offset = -4; offset <= 6; offset++) {
        months.push(new Date(today.getFullYear(), today.getMonth() + offset, 1));
    }

    monthList.innerHTML = '';

    months.forEach(date => {
        const button = document.createElement('button');
        button.type = 'button';
        const monthName = getMonthName(date.getMonth());
        const year = date.getFullYear();
        const monthValue = `${year}-${String(date.getMonth() + 1).padStart(2, '0')}`;

        button.textContent = `${monthName} ${year}`;
        button.dataset.month = monthValue;

        if (monthValue === selectedValue) {
            button.classList.add('selected');
        }

        button.addEventListener('click', function(e) {
            e.stopPropagation();
            selectMonth(this, monthBtn, monthList);
        });

        monthList.appendChild(button);
    });
}

function selectMonth(button, monthBtn, monthList) {
    monthBtn.textContent = button.textContent;
    monthBtn.dataset.month = button.dataset.month;

    document.querySelectorAll('.month-list button').forEach(btn => {
        btn.classList.remove('selected');
    });
    button.classList.add('selected');
    monthList.classList.remove('active');

    const url = new URL(window.location.href);
    url.searchParams.set('mois', button.dataset.month.replace('-', ''));
    window.location.href = url.toString();
}

function getMonthName(monthIndex) {
    const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                    'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    return months[monthIndex];
}
