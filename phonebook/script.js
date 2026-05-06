// ========== 1. Валидация форм перед отправкой ==========

// Форма входа
document.addEventListener('DOMContentLoaded', function() {
    
    // Форма логина
    const loginForm = document.querySelector('form[action="login.php"]');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const login = document.querySelector('input[name="login"]').value.trim();
            const password = document.querySelector('input[name="password"]').value;
            
            if (login === '') {
                e.preventDefault();
                showNotification('Введите логин', 'error');
                return false;
            }
            if (password === '') {
                e.preventDefault();
                showNotification('Введите пароль', 'error');
                return false;
            }
        });
    }
    
    // Форма поиска
    const searchForm = document.querySelector('.search-box');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const query = document.querySelector('input[name="query"]').value.trim();
            if (query === '') {
                e.preventDefault();
                showNotification('Введите текст для поиска', 'error');
                return false;
            }
        });
    }
    
    // Форма создания пользователя (админка)
    const createUserForm = document.querySelector('form input[name="create_user"]')?.closest('form');
    if (createUserForm) {
        createUserForm.addEventListener('submit', function(e) {
            const login = document.querySelector('input[name="login"]').value.trim();
            const password = document.querySelector('input[name="password"]').value;
            const fullName = document.querySelector('input[name="full_name"]').value.trim();
            
            if (login.length < 3) {
                e.preventDefault();
                showNotification('Логин должен быть не менее 3 символов', 'error');
                return false;
            }
            if (password.length < 4) {
                e.preventDefault();
                showNotification('Пароль должен быть не менее 4 символов', 'error');
                return false;
            }
            if (fullName === '') {
                e.preventDefault();
                showNotification('Введите ФИО', 'error');
                return false;
            }
        });
    }
    
    // Форма добавления контакта
    const addContactForm = document.querySelector('form input[name="add_contact"]')?.closest('form');
    if (addContactForm) {
        addContactForm.addEventListener('submit', function(e) {
            const phone = document.querySelector('input[name="phone"]').value.trim();
            const phoneRegex = /^[\d\+][\d\s\-\(\)]{5,20}$/;
            
            if (!phoneRegex.test(phone)) {
                e.preventDefault();
                showNotification('Введите корректный номер телефона', 'error');
                return false;
            }
        });
    }
});

// ========== 2. Подтверждение опасных действий ==========

// Функция для подтверждения (используется в админке при блокировке)
function confirmAction(message, callback) {
    if (confirm(message)) {
        if (callback) callback();
        return true;
    }
    return false;
}

// Автоматически добавляем подтверждение на все кнопки блокировки
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('button[name="toggle_search"]');
    toggleButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const action = btn.textContent.trim();
            const isBlock = action.includes('Запретить');
            const message = isBlock ? 
                'Вы уверены, что хотите ЗАПРЕТИТЬ поиск этому сотруднику?' : 
                'Вы уверены, что хотите РАЗРЕШИТЬ поиск этому сотруднику?';
            
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
});

// ========== 3. Живой поиск с автодополнением ==========

let searchTimeout;

function liveSearch() {
    const input = document.querySelector('input[name="query"]');
    if (!input) return;
    
    const resultsDiv = document.getElementById('live-results');
    if (!resultsDiv) {
        // Создаем контейнер для результатов
        const container = document.createElement('div');
        container.id = 'live-results';
        container.style.cssText = 'position: absolute; background: white; border: 1px solid #ddd; border-radius: 6px; max-height: 300px; overflow-y: auto; z-index: 1000; display: none; margin-top: 5px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);';
        input.parentNode.style.position = 'relative';
        input.parentNode.appendChild(container);
    }
    
    input.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        const resultsContainer = document.getElementById('live-results');
        
        if (query.length < 2) {
            resultsContainer.style.display = 'none';
            return;
        }
        
        searchTimeout = setTimeout(() => {
            // AJAX запрос для автодополнения
            fetch(`ajax_search.php?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        resultsContainer.innerHTML = data.map(item => 
                            `<div style="padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;" onclick="document.querySelector('input[name=\'query\']').value='${item.full_name}'; document.getElementById('live-results').style.display='none'; document.querySelector('.search-box button').click();">
                                <strong>${item.full_name}</strong><br>
                                <small style="color: #666;">${item.department} | ${item.phone}</small>
                            </div>`
                        ).join('');
                        resultsContainer.style.display = 'block';
                    } else {
                        resultsContainer.innerHTML = '<div style="padding: 10px; color: #888;">Ничего не найдено</div>';
                        resultsContainer.style.display = 'block';
                    }
                });
        }, 300);
    });
    
    // Скрываем результаты при клике вне
    document.addEventListener('click', function(e) {
        const container = document.getElementById('live-results');
        if (container && !container.contains(e.target) && e.target !== input) {
            container.style.display = 'none';
        }
    });
}

// ========== 4. Анимации и уведомления ==========

function showNotification(message, type = 'info') {
    // Создаем уведомление
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 8px;
        color: white;
        font-size: 14px;
        z-index: 9999;
        animation: slideIn 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    // Цвета
    const colors = {
        success: '#27ae60',
        error: '#e74c3c',
        info: '#3498db',
        warning: '#f39c12'
    };
    notification.style.backgroundColor = colors[type] || colors.info;
    
    document.body.appendChild(notification);
    
    // Удаляем через 3 секунды
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Добавляем CSS анимации
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .btn-loading {
        opacity: 0.6;
        pointer-events: none;
    }
    
    table tr:hover {
        background: #f8f9fa;
        transition: background 0.2s;
    }
`;
document.head.appendChild(style);

// ========== 5. Обработка загрузки кнопок ==========

function setButtonLoading(button, isLoading, originalText = null) {
    if (!button) return;
    
    if (isLoading) {
        button.dataset.originalText = button.textContent;
        button.textContent = '⏳ Загрузка...';
        button.classList.add('btn-loading');
        button.disabled = true;
    } else {
        button.textContent = button.dataset.originalText || originalText || button.textContent;
        button.classList.remove('btn-loading');
        button.disabled = false;
    }
}

// ========== 6. Копирование телефона в буфер обмена ==========

function copyToClipboard(phone, element) {
    navigator.clipboard.writeText(phone).then(() => {
        const originalText = element.textContent;
        element.textContent = '✅ Скопировано!';
        setTimeout(() => {
            element.textContent = originalText;
        }, 1500);
        showNotification(`Номер ${phone} скопирован`, 'success');
    }).catch(() => {
        showNotification('Не удалось скопировать', 'error');
    });
}

// Добавляем кнопки копирования к телефонам в таблицах
document.addEventListener('DOMContentLoaded', function() {
    const phoneCells = document.querySelectorAll('table td:nth-child(2)');
    phoneCells.forEach(cell => {
        const phone = cell.textContent.trim();
        if (phone && phone.match(/[\d\+\(\)\-]/)) {
            cell.style.cursor = 'pointer';
            cell.title = 'Нажмите, чтобы скопировать номер';
            cell.addEventListener('click', () => copyToClipboard(phone, cell));
        }
    });
});

// ========== 7. Фильтрация таблицы на клиенте ==========

function filterTable(tableId, searchInputId) {
    const input = document.getElementById(searchInputId);
    if (!input) return;
    
    input.addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const rows = table.getElementsByTagName('tr');
        
        for (let i = 1; i < rows.length; i++) {
            let found = false;
            const cells = rows[i].getElementsByTagName('td');
            for (let j = 0; j < cells.length; j++) {
                if (cells[j]) {
                    const text = cells[j].textContent || cells[j].innerText;
                    if (text.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            rows[i].style.display = found ? '' : 'none';
        }
    });
}

// Запускаем liveSearch при загрузке
liveSearch();

// ========== 8. Автообновление времени сессии ==========

let sessionTimer;
const SESSION_TIMEOUT = 30 * 60 * 1000; // 30 минут

function resetSessionTimer() {
    clearTimeout(sessionTimer);
    sessionTimer = setTimeout(() => {
        if (confirm('Сессия истекает через 1 минуту. Продолжить работу?')) {
            // Отправляем запрос на продление сессии
            fetch('keep_alive.php');
            resetSessionTimer();
        }
    }, SESSION_TIMEOUT - 60000);
}

// Запускаем таймер только на защищенных страницах
if (window.location.pathname.includes('dashboard.php') || window.location.pathname.includes('admin.php')) {
    resetSessionTimer();
    document.addEventListener('mousemove', resetSessionTimer);
    document.addEventListener('keypress', resetSessionTimer);
}

// ========== 9. Экспорт таблицы в CSV ==========

function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let row of rows) {
        const rowData = [];
        const cols = row.querySelectorAll('td, th');
        for (let col of cols) {
            rowData.push('"' + col.textContent.replace(/"/g, '""') + '"');
        }
        csv.push(rowData.join(','));
    }
    
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
    URL.revokeObjectURL(link.href);
    
    showNotification('Экспорт завершен', 'success');
}

console.log('✅ script.js загружен и готов к работе');