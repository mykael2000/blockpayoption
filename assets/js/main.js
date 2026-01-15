/**
 * Main JavaScript File
 * Contains all interactive features for the website
 */

// Toast Notification System
const toast = {
    show: function(message, type = 'info') {
        const toast = document.createElement('div');
        const bgColors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            info: 'bg-blue-500',
            warning: 'bg-yellow-500'
        };
        
        toast.className = `fixed top-4 right-4 ${bgColors[type] || bgColors.info} text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full z-50`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        // Slide in
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);
        
        // Slide out and remove
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
};

// Copy to Clipboard
function copyToClipboard(text, button = null) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            toast.show('Copied to clipboard!', 'success');
            if (button) {
                const originalText = button.innerHTML;
                button.innerHTML = '✓ Copied!';
                button.classList.add('bg-green-600');
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('bg-green-600');
                }, 2000);
            }
        }).catch(() => {
            fallbackCopyToClipboard(text);
        });
    } else {
        fallbackCopyToClipboard(text);
    }
}

// Fallback copy method for older browsers
function fallbackCopyToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    document.body.appendChild(textArea);
    textArea.select();
    try {
        document.execCommand('copy');
        toast.show('Copied to clipboard!', 'success');
    } catch (err) {
        toast.show('Failed to copy', 'error');
    }
    document.body.removeChild(textArea);
}

// Smooth Scrolling
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
});

// Mobile Menu Toggle
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    if (menu) {
        menu.classList.toggle('hidden');
    }
}

// Close mobile menu when clicking overlay
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', toggleMobileMenu);
    }
    
    const mobileMenuLinks = document.querySelectorAll('#mobile-menu a');
    mobileMenuLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 768) {
                toggleMobileMenu();
            }
        });
    });
});

// Search/Filter functionality
function filterItems(searchTerm, containerSelector, itemSelector) {
    const container = document.querySelector(containerSelector);
    if (!container) return;
    
    const items = container.querySelectorAll(itemSelector);
    const term = searchTerm.toLowerCase();
    let visibleCount = 0;
    
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(term)) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Show no results message
    const noResults = container.querySelector('.no-results');
    if (noResults) {
        noResults.style.display = visibleCount === 0 ? 'block' : 'none';
    }
}

// Image lazy loading
document.addEventListener('DOMContentLoaded', function() {
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => imageObserver.observe(img));
});

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('border-red-500');
            
            // Remove error class on input
            input.addEventListener('input', function() {
                this.classList.remove('border-red-500');
            }, { once: true });
        }
    });
    
    if (!isValid) {
        toast.show('Please fill in all required fields', 'error');
    }
    
    return isValid;
}

// Loading state for buttons
function setButtonLoading(button, loading = true) {
    if (loading) {
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> Loading...';
        button.disabled = true;
    } else {
        button.innerHTML = button.dataset.originalText || button.innerHTML;
        button.disabled = false;
    }
}

// AJAX helper
async function fetchJSON(url, options = {}) {
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('Fetch error:', error);
        toast.show('An error occurred. Please try again.', 'error');
        throw error;
    }
}

// Initialize tooltips (if needed)
document.addEventListener('DOMContentLoaded', function() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'absolute bg-gray-800 text-white text-xs rounded px-2 py-1 z-50';
            tooltip.textContent = this.dataset.tooltip;
            tooltip.id = 'tooltip-' + Math.random().toString(36).substr(2, 9);
            
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
            tooltip.style.left = (rect.left + (rect.width - tooltip.offsetWidth) / 2) + 'px';
            
            this.dataset.tooltipId = tooltip.id;
        });
        
        element.addEventListener('mouseleave', function() {
            const tooltipId = this.dataset.tooltipId;
            if (tooltipId) {
                const tooltip = document.getElementById(tooltipId);
                if (tooltip) {
                    document.body.removeChild(tooltip);
                }
            }
        });
    });
});

// Animate elements on scroll
document.addEventListener('DOMContentLoaded', function() {
    const animateElements = document.querySelectorAll('.animate-on-scroll');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
            }
        });
    }, { threshold: 0.1 });
    
    animateElements.forEach(el => observer.observe(el));
});

// Countdown timer for payment links
function startCountdown(elementId, targetDate) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const target = new Date(targetDate).getTime();
    
    const updateCountdown = () => {
        const now = new Date().getTime();
        const distance = target - now;
        
        if (distance < 0) {
            element.innerHTML = '<span class="text-red-600">Expired</span>';
            return;
        }
        
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        element.innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
        
        setTimeout(updateCountdown, 1000);
    };
    
    updateCountdown();
}

// Export functions for global access
window.copyToClipboard = copyToClipboard;
window.toggleMobileMenu = toggleMobileMenu;
window.filterItems = filterItems;
window.validateForm = validateForm;
window.setButtonLoading = setButtonLoading;
window.fetchJSON = fetchJSON;
window.toast = toast;
window.startCountdown = startCountdown;

/**
 * Filter payment methods by type (all, crypto, bank)
 */
function filterPaymentMethods(type) {
    const cards = document.querySelectorAll('.payment-method-card');
    const tabs = document.querySelectorAll('.filter-tab');
    
    // Update active tab styling
    tabs.forEach(tab => {
        const tabFilter = tab.getAttribute('data-filter');
        if (tabFilter === type) {
            tab.classList.remove('bg-gray-200', 'text-gray-700', 'hover:bg-purple-100', 'hover:bg-emerald-100');
            if (type === 'all') {
                tab.classList.add('bg-gray-600', 'text-white');
            } else if (type === 'crypto') {
                tab.classList.add('bg-purple-600', 'text-white');
            } else if (type === 'bank') {
                tab.classList.add('bg-emerald-600', 'text-white');
            }
        } else {
            tab.classList.remove('bg-gray-600', 'bg-purple-600', 'bg-emerald-600', 'text-white');
            tab.classList.add('bg-gray-200', 'text-gray-700');
            if (tabFilter === 'crypto') {
                tab.classList.add('hover:bg-purple-100');
            } else if (tabFilter === 'bank') {
                tab.classList.add('hover:bg-emerald-100');
            }
        }
    });
    
    // Filter cards with smooth transition
    cards.forEach(card => {
        const cardType = card.getAttribute('data-payment-type');
        
        if (type === 'all' || cardType === type) {
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            card.style.display = '';
            
            setTimeout(() => {
                card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                card.style.opacity = '1';
                card.style.transform = 'scale(1)';
            }, 10);
        } else {
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            
            setTimeout(() => {
                card.style.display = 'none';
            }, 300);
        }
    });
}

/**
 * Filter featured payment methods on homepage
 */
function filterFeaturedMethods(type) {
    const cards = document.querySelectorAll('.featured-payment-card');
    const tabs = document.querySelectorAll('.featured-filter-tab');
    
    // Update active tab styling
    tabs.forEach(tab => {
        const tabFilter = tab.getAttribute('data-featured-filter');
        if (tabFilter === type) {
            tab.classList.remove('bg-gray-200', 'text-gray-700', 'hover:bg-purple-100', 'hover:bg-emerald-100');
            if (type === 'all') {
                tab.classList.add('bg-gray-600', 'text-white');
            } else if (type === 'crypto') {
                tab.classList.add('bg-purple-600', 'text-white');
            } else if (type === 'bank') {
                tab.classList.add('bg-emerald-600', 'text-white');
            }
        } else {
            tab.classList.remove('bg-gray-600', 'bg-purple-600', 'bg-emerald-600', 'text-white');
            tab.classList.add('bg-gray-200', 'text-gray-700');
            if (tabFilter === 'crypto') {
                tab.classList.add('hover:bg-purple-100');
            } else if (tabFilter === 'bank') {
                tab.classList.add('hover:bg-emerald-100');
            }
        }
    });
    
    // Filter cards with smooth transition
    cards.forEach(card => {
        const cardType = card.getAttribute('data-payment-type');
        
        if (type === 'all' || cardType === type) {
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            card.style.display = '';
            
            setTimeout(() => {
                card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                card.style.opacity = '1';
                card.style.transform = 'scale(1)';
            }, 10);
        } else {
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            
            setTimeout(() => {
                card.style.display = 'none';
            }, 300);
        }
    });
}

// Export new filter functions
window.filterPaymentMethods = filterPaymentMethods;
window.filterFeaturedMethods = filterFeaturedMethods;
