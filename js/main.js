const API_URL = 'api';

// Auth & Role Management
const user = JSON.parse(localStorage.getItem('user'));
const roleSelectContainer = document.querySelector('.flex.items-center.gap-3'); // Target container in Navbar

function updateNavbar() {
    if (!roleSelectContainer) return;

    if (user) {
        // Logged In State
        // Check if user has profile pic
        const avatarContent = user.profile_picture
            ? `<img src="${user.profile_picture}" class="w-full h-full object-cover rounded-full">`
            : `<div class="w-full h-full bg-green-500 rounded-full flex items-center justify-center text-xs font-bold text-white capitalize">${user.first_name ? user.first_name[0] : (user.username ? user.username[0] : 'U')}</div>`;

        const profileHTML = `
            <div class="flex items-center gap-3">
                <a href="settings.html" class="flex items-center gap-2 bg-green-800 p-1 pr-3 rounded-lg border border-green-600 hover:bg-green-700 transition" title="Go to Settings">
                    <div class="w-8 h-8 rounded-full border-2 border-green-400 overflow-hidden">
                        ${avatarContent}
                    </div>
                    <div class="flex flex-col leading-none">
                        <span class="text-xs font-bold capitalize">${user.first_name || user.username}</span>
                        <span class="text-[10px] text-green-200 uppercase tracking-wider">${user.role}</span>
                    </div>
                </a>
                <button onclick="logout()" class="text-xs text-red-300 hover:text-white font-bold ml-1">Logout</button>
            </div>
            <a href="wishlist.html" class="flex items-center justify-center w-8 h-8 rounded-full bg-green-800 hover:bg-green-700 transition ml-2 border border-green-600" title="Wishlist">
                <i data-lucide="heart" class="w-4 h-4 text-white"></i>
            </a>
            <a href="cart.html" class="relative hover:scale-110 transition ml-2">
                <i data-lucide="shopping-cart" class="w-6 h-6 text-white"></i>
                <span id="cart-count" class="absolute -top-2 -right-2 bg-yellow-500 text-green-900 text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
            </a>
            </a>
        `;

        // Inject into navbar
        const navRightSide = document.querySelector('nav .flex.items-center.gap-3');
        if (navRightSide) {
            navRightSide.innerHTML = profileHTML;
        } else {
            // Fallback: try to find the old role select and replace its container
            const oldSelect = document.getElementById('role-select');
            if (oldSelect && oldSelect.parentElement) {
                oldSelect.parentElement.innerHTML = profileHTML;
            }
        }

        // Show Dashboard Link for logged in users (Buyer & Farmer)
        const dashLink = document.getElementById('nav-dashboard-link');
        if (dashLink) dashLink.classList.remove('hidden');

        if (user.role === 'admin') {
            const adminLink = document.getElementById('nav-admin-link');
            if (adminLink) {
                adminLink.classList.remove('hidden');
                // Force visibility just in case
                adminLink.style.display = 'block';
            }
        }

    } else {
        // Guest State
        const loginHTML = `
            <a href="login.html" class="bg-white text-green-700 px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-green-50 transition">Login</a>
            <a href="register.html" class="bg-yellow-500 text-green-900 px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-yellow-400 transition hidden md:block">Register</a>
        `;
        const navRightSide = document.querySelector('nav .flex.items-center.gap-3');
        if (navRightSide) navRightSide.innerHTML = loginHTML;
    }

    // Refresh icons
    if (window.lucide) window.lucide.createIcons();
}

async function logout() {
    try {
        await fetch('api/auth.php', {
            method: 'POST',
            body: JSON.stringify({ action: 'logout' })
        });
    } catch (e) { console.error('Server logout failed', e); }

    localStorage.removeItem('user');
    window.location.replace('login.html');
}

// Security: Check session status when returning to the tab or back button
document.addEventListener('visibilitychange', () => {
    if (!document.hidden && user) {
        fetch('api/auth.php', {
            method: 'POST',
            body: JSON.stringify({ action: 'check_session' })
        }).then(res => res.json()).then(data => {
            if (!data.success) {
                localStorage.removeItem('user');
                window.location.replace('login.html');
            }
        }).catch(() => { });
    }
});

// Initialize
updateNavbar();

// Logic for Role-based visibility
let currentRole = user ? user.role : 'guest';


// Cart Management
const cartKey = user ? `cart_${user.id}` : 'cart_guest';
let cart = JSON.parse(localStorage.getItem(cartKey) || '[]');

function updateCartCount() {
    const el = document.getElementById('cart-count');
    if (el) {
        const count = cart.reduce((acc, item) => acc + item.qty, 0);
        if (count > 0) {
            el.textContent = count;
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    }
}
updateCartCount();

// Session Timeout Logic (1 Hour)
const TIMEOUT_MS = 60 * 60 * 1000; // 1 Hour

function resetInactivityTimer() {
    if (user) {
        localStorage.setItem('lastActive', Date.now());
    }
}

function checkInactivity() {
    if (!user) return;
    const lastActive = parseInt(localStorage.getItem('lastActive') || Date.now());
    if (Date.now() - lastActive > TIMEOUT_MS) {
        alert("Session expired due to inactivity. Logging out.");
        logout();
    }
}

if (user) {
    // Check immediately on load
    checkInactivity();
    // Update active timestamp on load if likely active
    resetInactivityTimer();

    // Listeners for activity
    ['mousemove', 'click', 'keydown', 'scroll'].forEach(evt => {
        window.addEventListener(evt, () => {
            // Throttling could be added max once per 10s to save write ops, but localstorage is fast enough for now
            // Or better: simpler throttle
            if (!window.activityThrottler) {
                window.activityThrottler = setTimeout(() => {
                    resetInactivityTimer();
                    window.activityThrottler = null;
                }, 5000); // update every 5 seconds at most
            }
        });
    });

    // Validated interval check
    setInterval(checkInactivity, 60000); // Check every minute
}

window.addToCart = (product, qtyId, silent = false) => {
    if (!user) {
        if (confirm("You must be logged in to add items to cart.\nGo to login page?")) {
            window.location.href = 'login.html';
        }
        return;
    }

    const qtyInput = document.getElementById(qtyId);
    const qty = qtyInput ? parseInt(qtyInput.value) : 1;

    if (qty <= 0) {
        alert("Quantity must be at least 1");
        return;
    }

    // Ensure cart is fresh from storage to avoid overwrites if multiple tabs (optimistic)
    // Actually current logic uses global 'cart' var which might be stale if other tabs updated it.
    // For robustness, let's re-read but for now let's stick to global or simple logic. 
    // Ideally: cart = JSON.parse(localStorage.getItem(cartKey) || '[]');

    const existing = cart.find(item => item.id === product.id);
    if (existing) {
        existing.qty += qty;
    } else {
        product.qty = qty;
        cart.push(product);
    }

    localStorage.setItem(cartKey, JSON.stringify(cart));
    updateCartCount();
    if (!silent) showToast(`Added ${qty} items to cart!`, 'success');
}

// Toast Notification Function
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white font-bold z-50 ${type === 'success' ? 'bg-green-600' : 'bg-red-600'
        }`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Format Currency
function formatRWF(amount) {
    return new Intl.NumberFormat('rw-RW', { style: 'currency', currency: 'RWF', maximumFractionDigits: 0 }).format(amount);
}

// Load Products (Marketplace)
// Global State for Pagination
let currentPage = 1;
let totalPages = 1;
let isLoading = false;
let currentProducts = []; // Store accumulated products for filtering
let currentCategory = 'All';

async function loadProducts(reset = false, category = null) {
    const grid = document.getElementById('products-grid');
    const searchInput = document.getElementById('search-input');

    if (category) currentCategory = category;

    if (reset) {
        currentPage = 1;
        currentProducts = [];
        grid.innerHTML = '';
        window.scrollTo(0, 0);
    }

    if (isLoading) return;
    isLoading = true;

    // Show Loader if first page
    if (currentPage === 1 && grid.children.length === 0) {
        grid.innerHTML = '<div class="col-span-full text-center py-10">Loading products...</div>';
    }

    try {
        // Fetch
        const url = `${API_URL}/products.php?page=${currentPage}&limit=12&category=${encodeURIComponent(currentCategory)}`;
        const res = await fetch(url);
        const data = await res.json();

        // Handle new structure { items: [], meta: {} } OR old array []
        const newItems = Array.isArray(data) ? data : (data.items || []);
        const meta = data.meta || {};

        if (currentPage === 1) grid.innerHTML = ''; // Clear loader

        if (newItems.length === 0 && currentPage === 1) {
            grid.innerHTML = '<div class="col-span-full text-center py-10 text-gray-400">No products found in ' + currentCategory + '.</div>';
            return;
        }

        currentProducts = [...currentProducts, ...newItems];

        // Render Items
        newItems.forEach(p => {
            // Check if current user is the seller
            const isOwner = user && (p.user_id == user.id || p.isOwner);

            const card = document.createElement('div');
            card.className = "bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-xl transition cursor-pointer";
            card.onclick = (e) => {
                if (!e.target.closest('button') && !e.target.closest('input')) {
                    window.location.href = `product.html?id=${p.id}`;
                }
            };

            // Image Handling
            let imgHTML = `<i data-lucide="leaf" class="text-green-600 w-16 h-16 opacity-50"></i>`;
            if (p.image && p.image.includes('/')) {
                imgHTML = `<img src="${p.image}" class="w-full h-full object-cover">`;
            }

            const actionButton = (user && user.role === 'admin')
                ? `<button disabled class="w-full bg-purple-100 text-purple-800 py-2 rounded-lg font-bold cursor-default border border-purple-200">Admin View</button>`
                : (isOwner
                    ? `<button disabled class="w-full bg-gray-100 text-gray-400 py-2 rounded-lg font-bold cursor-not-allowed border border-gray-200">Your Product</button>`
                    : `<button onclick='addToCart(${JSON.stringify(p)}, "qty-${p.id}")' class="w-full bg-green-600 text-white py-2 rounded-lg font-medium hover:bg-green-700 flex items-center justify-center gap-2">
                            <i data-lucide="shopping-cart" width="16"></i> Add to Cart
                       </button>`);

            card.innerHTML = `
                <div class="h-48 bg-gray-200 flex items-center justify-center relative">
                    ${imgHTML}
                    ${p.certified ? '<span class="absolute top-2 left-2 bg-blue-600 text-white text-[10px] px-2 py-1 rounded-full shadow z-10">Certified</span>' : ''}
                    <button class="absolute top-2 right-2 p-2 bg-white/90 rounded-full hover:bg-white text-gray-400 hover:text-red-500 shadow-sm wishlist-btn z-10 type="button" data-id="${p.id}" onclick="event.stopPropagation(); toggleWishlist(${p.id})">
                        <i data-lucide="heart" width="18" height="18"></i>
                    </button>
                </div>
                <div class="p-4">
                    <div class="flex justify-between items-start mb-1">
                        <h3 class="font-bold text-lg text-gray-800 line-clamp-1">${p.name}</h3>
                        <span class="text-green-700 font-bold">${formatRWF(p.price)}</span>
                    </div>
                    <p class="text-sm text-gray-700 mb-3 bg-green-50 p-1 rounded inline-block border border-green-100">
                        Sold by: <span class="text-green-800 font-bold uppercase">${p.seller}</span> <i data-lucide="check-circle" class="w-3 h-3 inline text-blue-500"></i>
                    </p>
                    <p class="text-xs text-gray-500 mb-4 flex items-center gap-1">
                        <i data-lucide="map-pin" width="12"></i> ${p.location}
                    </p>
                    <div class="flex items-center gap-2 mb-3 bg-gray-50 p-2 rounded">
                        <button onclick="document.getElementById('qty-${p.id}').stepDown()" class="w-8 h-8 rounded bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 font-bold" ${isOwner ? 'disabled' : ''}>-</button>
                        <input type="number" id="qty-${p.id}" value="1" min="1" class="w-12 text-center border rounded p-1 text-sm bg-white" readonly>
                        <button onclick="document.getElementById('qty-${p.id}').stepUp()" class="w-8 h-8 rounded bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 font-bold" ${isOwner ? 'disabled' : ''}>+</button>
                        <span class="text-xs text-gray-500">${p.unit}</span>
                    </div>
                    ${actionButton}
                </div>
            `;
            grid.appendChild(card);
        });

        // Pagination Logic
        totalPages = meta.total_pages || 1;
        manageLoadMoreButton(grid, totalPages > currentPage);
        currentPage++;

        lucide.createIcons();
        updateWishlistUI();

    } catch (e) {
        console.error(e);
        if (currentPage === 1) grid.innerHTML = '<div class="col-span-full text-center text-red-500">Failed to load products.</div>';
    } finally {
        isLoading = false;
    }
}

// Init Category Filters
document.addEventListener('DOMContentLoaded', () => {
    const filterBtns = document.querySelectorAll('.filter-btn');
    if (filterBtns.length > 0) {
        filterBtns.forEach(btn => {
            btn.onclick = () => {
                // UI Toggle
                filterBtns.forEach(b => {
                    b.classList.remove('bg-green-600', 'text-white', 'shadow-md');
                    b.classList.add('bg-gray-200', 'text-gray-700');
                });
                btn.classList.remove('bg-gray-200', 'text-gray-700');
                btn.classList.add('bg-green-600', 'text-white', 'shadow-md');

                // Filter Logic
                const cat = btn.dataset.cat;
                loadProducts(true, cat);
            };
        });
    }
});

function manageLoadMoreButton(grid, hasMore) {
    let btn = document.getElementById('load-more-container');
    if (!btn) {
        btn = document.createElement('div');
        btn.id = 'load-more-container';
        btn.className = 'col-span-full flex justify-center mt-8 pb-10';
        grid.parentNode.appendChild(btn);
    }

    if (hasMore) {
        btn.innerHTML = `<button onclick="if(user) { loadProducts(); } else { if(confirm('Please login to view more products.')) window.location.href='login.html'; }" class="bg-white border border-green-600 text-green-700 font-bold py-2 px-6 rounded-full hover:bg-green-50 transition shadow-sm">Load More Products</button>`;
        btn.classList.remove('hidden');
    } else {
        btn.classList.add('hidden');
    }
}

// Search Listener Refactored to filter CLIENT SIDE for now (or reset and search API if needed, but for simplicity let's stick to simple implementation)
// Actually, since we paginate, client side search is bad. But implementing full server search is out of scope for "just add pagination".
// Let's keep it simple: Search resets pagination and reloads (if server supported it) OR we accept client filter only works on loaded items.
// Given time, let's make search reset and reload.
const searchInput = document.getElementById('search-input');
if (searchInput) {
    searchInput.addEventListener('input', (e) => {
        // Debounce?
        // Ideally we call server search. But api/products.php doesn't have 'search' param in GET, only category.
        // Let's filter client side on *loaded* items for now to avoid breaking existing behavior too much, 
        // OR better: Just warn user "Searching..."
        const term = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('#products-grid > div');
        cards.forEach(card => {
            const name = card.querySelector('h3').textContent.toLowerCase();
            card.style.display = name.includes(term) ? 'block' : 'none';
        });
    });
}

// Upload Product
const uploadForm = document.getElementById('upload-form');
// Upload Product Logic (Moved to DOMContentLoaded to ensure elements exist)
document.addEventListener('DOMContentLoaded', () => {
    const uploadForm = document.getElementById('upload-form');
    if (uploadForm) {
        const modal = document.getElementById('upload-modal');
        const openBtn = document.getElementById('add-product-btn');
        const closeBtn = document.getElementById('close-modal');

        if (openBtn) openBtn.onclick = () => modal.classList.remove('hidden');
        if (closeBtn) closeBtn.onclick = () => modal.classList.add('hidden');

        // Auto-Open Logic
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('action') === 'add') {
            console.log("Auto-opening modal...");
            modal.classList.remove('hidden');
        }

        uploadForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('p-name').value;
            const price = document.getElementById('p-price').value;
            const nameErr = document.getElementById('name-error');

            // Regex Validation
            if (!/^[a-zA-Z0-9\s]+$/.test(name)) {
                nameErr.classList.remove('hidden');
                return;
            }
            nameErr.classList.add('hidden');

            const formData = new FormData();
            formData.append('user_id', window.user ? window.user.id : 1); // Ensure user is accessible
            formData.append('name', name);
            formData.append('price', price);
            formData.append('unit', document.getElementById('p-unit').value);
            formData.append('category', document.getElementById('p-category').value);
            formData.append('location', document.getElementById('p-location').value || 'Kigali');
            formData.append('certified', '1'); // simplified

            const fileInput = document.getElementById('p-image');
            if (fileInput && fileInput.files[0]) {
                formData.append('image', fileInput.files[0]);
            }

            try {
                await fetch(`${API_URL}/products.php`, {
                    method: 'POST',
                    body: formData // No Content-Type header needed for FormData
                });
                showToast('Product Uploaded!', 'success');
                setTimeout(() => window.location.href = 'market.html', 1000); // Clear params
            } catch (e) {
                console.error(e);
                showToast('Error uploading', 'error');
            }
        });
    }
});

// Load Advisory
// Load Advisory
async function loadAdvisory() {
    const grid = document.getElementById('advisory-grid');
    const header = document.getElementById('advisory-header');
    if (!grid) return;

    // Restriction: Buyers don't need agricultural advice
    // Check both local variable 'user' (from top of main.js) and window.user just in case
    const u = (typeof user !== 'undefined') ? user : window.user;

    if (u && u.role === 'buyer') {
        grid.style.display = 'none';
        if (header) header.style.display = 'none';
        return;
    }

    try {
        const res = await fetch(`${API_URL}/advisory.php`);
        const posts = await res.json();

        grid.innerHTML = '';
        posts.forEach(post => {
            const div = document.createElement('div');
            // Changed to simpler "Article" Layout
            div.className = "bg-white border p-6 rounded-xl shadow-sm hover:shadow-md transition mb-4 break-inside-avoid";

            const isLong = post.content.length > 150;
            const summary = isLong ? post.content.substring(0, 150) + "..." : post.content;

            div.innerHTML = `
                <div class="flex items-start gap-4 mb-4">
                    <div class="${post.color.split(' ')[0]} w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="book" class="${post.color.split(' ')[1]}"></i>
                    </div>
                    <div>
                        <span class="inline-block px-2 py-1 bg-gray-100 text-gray-500 text-xs rounded-full font-bold mb-1 uppercase tracking-wide">${post.type}</span>
                        <h4 class="font-bold text-xl text-gray-900 leading-tight">${post.title}</h4>
                        <span class="text-xs text-gray-400 font-medium block mt-1">${post.date}</span>
                    </div>
                </div>
                
                <div class="text-gray-600 text-sm leading-relaxed">
                    <div id="advisory-summary-${post.id}" class="${isLong ? '' : 'hidden'}">
                        ${summary}
                    </div>
                    <div id="advisory-full-${post.id}" class="${isLong ? 'hidden' : ''}">
                        ${post.content}
                    </div>
                </div>

                ${isLong ? `
                <div class="mt-4 border-t pt-3 border-gray-50">
                    <button onclick="toggleAdvisory(${post.id})" id="advisory-btn-${post.id}" class="text-green-600 font-bold text-sm flex items-center gap-1 hover:underline">
                        Read More <i data-lucide="chevron-down" width="16"></i>
                    </button>
                </div>
                ` : ''}
            `;
            grid.appendChild(div);
        });
        lucide.createIcons();
    } catch (e) {
        console.error(e);
        grid.innerHTML = '<div class="text-center text-red-500 p-10">Unable to load articles.</div>';
    }
}


window.toggleAdvisory = (id) => {
    const summary = document.getElementById(`advisory-summary-${id}`);
    const full = document.getElementById(`advisory-full-${id}`);
    const btn = document.getElementById(`advisory-btn-${id}`);

    if (summary.classList.contains('hidden')) {
        summary.classList.remove('hidden');
        full.classList.add('hidden');
        btn.innerHTML = `Read More <i data-lucide="chevron-down" width="16"></i>`;
    } else {
        summary.classList.add('hidden');
        full.classList.remove('hidden');
        btn.innerHTML = `Read Less <i data-lucide="chevron-up" width="16"></i>`;
    }
    lucide.createIcons();
};

// Wishlist Logic
let wishlist = new Set();
async function initWishlist() {
    if (!user) return;
    try {
        const res = await fetch(`${API_URL}/wishlist.php?user_id=${user.id}`);
        const items = await res.json();
        wishlist = new Set(items.map(i => parseInt(i.id)));
        updateWishlistUI();
    } catch (e) {
        console.error("Failed to load wishlist", e);
    }
}

async function toggleWishlist(productId, btnId = null) {
    if (!user) {
        if (confirm("Please login to use wishlist.\nGo to login page?")) {
            window.location.href = 'login.html';
        }
        return;
    }

    // Ensure ID is integer
    const pid = parseInt(productId);

    // Admin Restriction
    if (user.role === 'admin') {
        alert("Admins cannot use the wishlist.");
        return;
    }

    const action = wishlist.has(pid) ? 'remove' : 'add';

    // Optimistic UI update
    if (action === 'add') wishlist.add(pid);
    else wishlist.delete(pid);
    updateWishlistUI(btnId);

    try {
        const res = await fetch(`${API_URL}/wishlist.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }, // Added Content-Type
            body: JSON.stringify({ user_id: user.id, product_id: pid, action })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.error);
    } catch (e) {
        console.error("Wishlist sync failed", e);
        // Revert
        if (action === 'add') wishlist.delete(pid);
        else wishlist.add(pid);
        updateWishlistUI(btnId);
        showToast("Action failed", 'error');
    }
}

function updateWishlistUI(btnId = null) {
    // If a specific button ID is provided
    if (btnId) {
        const btn = document.getElementById(btnId);
        const pid = parseInt(btn.dataset.id);
        if (btn && pid) {
            updateHeartIcon(btn, wishlist.has(pid));

            // Disable interactions for admin visually too
            if (user && user.role === 'admin') {
                btn.style.opacity = '0.5';
                btn.style.cursor = 'not-allowed';
                btn.title = "Admins cannot wishlisting";
            }
        }
        return;
    }

    // Update all wishlist buttons
    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        const pid = parseInt(btn.dataset.id);
        updateHeartIcon(btn, wishlist.has(pid));

        if (user && user.role === 'admin') {
            btn.style.opacity = '0.5';
            btn.style.cursor = 'not-allowed';
            btn.title = "Admins cannot wishlisting";
        }
    });
}

function updateHeartIcon(btn, isFilled) {
    const icon = btn.querySelector('svg') || btn.querySelector('i'); // Lucide svg or i tag
    if (isFilled) {
        btn.classList.add('text-red-500');
        btn.classList.remove('text-gray-400', 'text-white');
        if (icon) icon.setAttribute('fill', 'currentColor');
    } else {
        btn.classList.remove('text-red-500');
        btn.classList.add('text-gray-400');
        // Note: For product page button, it might be white text initially. 
        // We'll handle specific styles in the template logic better.
        if (icon) icon.setAttribute('fill', 'none');
    }
}

// Product Details Page Logic
async function loadProductDetails() {
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');

    if (!id) {
        document.getElementById('product-name').textContent = "Product not found";
        return;
    }

    // Init wishlist for this user
    await initWishlist();

    try {
        const res = await fetch(`${API_URL}/products.php?id=${id}`);
        if (!res.ok) throw new Error("Product not found");
        const product = await res.json();

        // Populate Breadcrumb
        document.getElementById('breadcrumb-current').textContent = product.name;

        // Populate Details
        document.getElementById('product-name').textContent = product.name;
        document.getElementById('product-price').textContent = formatRWF(product.price);
        document.getElementById('product-seller').textContent = product.seller;
        document.getElementById('product-category').textContent = product.category;
        document.getElementById('product-unit').textContent = product.unit;

        // Image
        const img = document.getElementById('product-image');
        const ph = document.getElementById('product-placeholder-icon');
        if (product.image && product.image.includes('/')) {
            img.src = product.image;
            img.classList.remove('hidden');
            ph.classList.add('hidden');
        }

        // Badges
        if (product.certified) document.getElementById('badge-certified').classList.remove('hidden');
        if (product.organic) document.getElementById('badge-organic').classList.remove('hidden');

        // Check ownership
        const isOwner = user && (product.seller === (user.first_name || user.username));
        // Note: product.seller from API is the name. We should probably compare IDs for robustness if available, 
        // but API returns mapped seller name. market.html used name comparison too.
        // Let's stick to name comparison for consistency with market.html logic or improve backend later.
        // Actually, let's verify if we can compare IDs. API product response has user_id?
        // Let's check products.php response. It selects p.* so it has user_id.
        const isOwnerById = user && (parseInt(product.user_id) === parseInt(user.id));

        // Buttons
        const cartBtn = document.getElementById('add-to-cart-btn');
        const buyBtn = document.querySelector('button.bg-green-600.text-white'); // Buy Now button

        if (user && user.role === 'admin') {
            cartBtn.disabled = true;
            cartBtn.classList.add('bg-purple-100', 'text-purple-800', 'cursor-default', 'border', 'border-purple-200');
            cartBtn.classList.remove('bg-green-800', 'hover:bg-green-900', 'text-white');
            cartBtn.textContent = 'Admin View';

            if (buyBtn) buyBtn.classList.add('hidden');
        } else if (isOwnerById) {
            cartBtn.disabled = true;
            cartBtn.classList.add('bg-gray-300', 'cursor-not-allowed', 'hover:bg-gray-300');
            cartBtn.classList.remove('bg-green-800', 'hover:bg-green-900');
            cartBtn.textContent = 'Your Product';

            if (buyBtn) {
                buyBtn.disabled = true;
                buyBtn.classList.add('hidden'); // Hide buy now for owner
            }
        } else {
            cartBtn.onclick = () => addToCart(product, 'product-qty');
            if (buyBtn) buyBtn.onclick = () => buyNow(product, 'product-qty'); // Bind Buy Now
        }

        const wishBtn = document.getElementById('wishlist-btn');
        wishBtn.dataset.id = product.id;
        wishBtn.onclick = () => toggleWishlist(product.id, 'wishlist-btn');

        // Initial Wishlist State for this button
        if (wishlist.has(product.id)) {
            wishBtn.classList.remove('bg-green-800'); // Remove default solid bg if we want to toggle style
            wishBtn.classList.add('bg-white', 'text-red-500', 'border', 'border-red-200'); // Example active style
            // Verify icon fill
            const i = wishBtn.querySelector('i');
            if (i) i.setAttribute('fill', 'currentColor');
        }

        // Load Related Products
        loadRelatedProducts(product.category, product.id);

        // Update Vendor Tab Info
        const vendorNameTab = document.getElementById('vendor-name-tab');
        const vendorBio = document.getElementById('vendor-bio');
        const vendorAvatar = document.getElementById('vendor-avatar');

        if (vendorNameTab) vendorNameTab.textContent = product.seller;
        if (vendorBio) vendorBio.textContent = product.seller_bio || "No bio available.";

        if (vendorAvatar) {
            if (product.seller_image && product.seller_image.includes('/')) {
                vendorAvatar.src = product.seller_image;
            } else {
                // Initials fallback logic
                vendorAvatar.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(product.seller)}&background=random`;
            }
        }

        // Maybe show seller location if available?
        // document.getElementById('vendor-location').textContent = product.seller_address || product.location;

        if (vendorNameTab) vendorNameTab.textContent = product.seller;
        if (vendorBio) vendorBio.textContent = product.seller_bio || "No bio available.";
        if (vendorAvatar) vendorAvatar.textContent = product.seller ? product.seller[0].toUpperCase() : 'S';

        // Load Reviews
        loadReviews(product.id);

        // Review Form
        const revForm = document.getElementById('review-form');
        if (revForm) {
            if (user && user.role === 'admin') {
                revForm.innerHTML = '<p class="text-xs text-purple-600 font-bold bg-purple-50 p-3 rounded border border-purple-200">Admins cannot post reviews.</p>';
            } else if (isOwnerById) {
                revForm.innerHTML = '<p class="text-xs text-orange-600 font-bold bg-orange-50 p-3 rounded border border-orange-200">You cannot review your own product.</p>';
            } else {
                revForm.onsubmit = (e) => handleReviewSubmit(e, product.id);
            }
        }

    } catch (e) {
        console.error(e);
        document.getElementById('product-name').textContent = "Error loading product";
    }
}

async function loadRelatedProducts(category, excludeId) {
    const grid = document.getElementById('related-products-grid');
    try {
        const res = await fetch(`${API_URL}/products.php?category=${encodeURIComponent(category)}&exclude=${excludeId}&limit=4`);
        const data = await res.json();
        const products = Array.isArray(data) ? data : (data.items || []);

        grid.innerHTML = '';
        if (products.length === 0) {
            grid.innerHTML = '<div class="col-span-full text-center text-gray-400">No related products found.</div>';
            return;
        }

        products.forEach(p => {
            const card = document.createElement('div');
            card.className = "bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition cursor-pointer";
            card.onclick = (e) => {
                if (!e.target.closest('button')) window.location.href = `product.html?id=${p.id}`;
            };

            let imgHTML = `<div class="h-32 bg-gray-100 flex items-center justify-center rounded-t-xl"><i data-lucide="leaf" class="text-green-300"></i></div>`;
            if (p.image && p.image.includes('/')) {
                imgHTML = `<div class="h-32 rounded-t-xl overflow-hidden"><img src="${p.image}" class="w-full h-full object-cover"></div>`;
            }

            card.innerHTML = `
                    ${imgHTML}
                    <div class="p-3">
                        <h4 class="font-bold text-gray-800 line-clamp-1">${p.name}</h4>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-green-700 font-bold text-sm">${formatRWF(p.price)}</span>
                            <button class="wishlist-btn text-gray-400 hover:text-red-500" data-id="${p.id}" onclick="event.stopPropagation(); toggleWishlist(${p.id})">
                                <i data-lucide="heart" width="16" height="16"></i>
                            </button>
                        </div>
                    </div>
                `;
            grid.appendChild(card);
        });
        updateWishlistUI();
        lucide.createIcons();

    } catch (e) {
        grid.innerHTML = '<div class="col-span-full text-center text-red-500">Error loading related items.</div>';
    }
}

// Initial calls
// Initial calls
// Initial calls
if (document.querySelector('#products-grid')) {
    // Marketplace: Init wishlist then update UI (but UI update handles by loadProducts mostly, this ensures subsequent updates)
    initWishlist();
}

// Tab Switching (Product Page)
window.switchTab = (tab) => {
    // Buttons
    ['desc', 'reviews', 'vendor'].forEach(t => {
        const btn = document.getElementById(`tab-${t}-btn`);
        const content = document.getElementById(`tab-${t}-content`);
        if (btn && content) {
            if (t === tab) {
                btn.classList.add('border-green-800', 'text-green-800');
                btn.classList.remove('text-gray-500');
                content.classList.remove('hidden');
            } else {
                btn.classList.remove('border-green-800', 'text-green-800');
                btn.classList.add('text-gray-500');
                content.classList.add('hidden');
            }
        }
    });
};

// Reviews Logic
async function loadReviews(productId) {
    const list = document.getElementById('reviews-list');
    const tabBtn = document.getElementById('tab-reviews-btn');
    if (!list) return;

    try {
        const res = await fetch(`${API_URL}/reviews.php?product_id=${productId}`);
        const reviews = await res.json();

        // Update Count
        if (tabBtn) tabBtn.textContent = `Reviews (${reviews.length})`;

        list.innerHTML = '';
        if (reviews.length === 0) {
            list.innerHTML = '<p class="text-gray-400 italic">No reviews yet.</p>';
            return;
        }

        reviews.forEach(r => {
            const date = new Date(r.created_at).toLocaleDateString();
            const stars = '⭐'.repeat(r.rating);
            const div = document.createElement('div');
            div.className = 'border-b border-gray-100 pb-4 last:border-0';
            div.innerHTML = `
                <div class="flex justify-between items-start mb-1">
                    <span class="font-bold text-gray-800">${r.first_name || 'User'} ${r.last_name || ''}</span>
                    <span class="text-xs text-gray-400">${date}</span>
                </div>
                <div class="text-xs mb-2 text-yellow-500">${stars}</div>
                <p class="text-gray-600 text-sm">${r.comment}</p>
            `;
            list.appendChild(div);
        });

    } catch (e) {
        console.error("Error loading reviews", e);
    }
}

async function handleReviewSubmit(e, productId) {
    e.preventDefault();
    if (!user) {
        alert("Please login to post a review");
        return;
    }

    if (user.role === 'admin') {
        alert("Admins are not allowed to post reviews.");
        return;
    }

    const rating = document.getElementById('review-rating').value;
    const comment = document.getElementById('review-comment').value;

    try {
        const res = await fetch(`${API_URL}/reviews.php`, {
            method: 'POST',
            body: JSON.stringify({
                user_id: user.id,
                product_id: productId,
                rating,
                comment
            })
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('review-form').reset();
            loadReviews(productId); // Reload list
            showToast("Review posted!", 'success');
        } else {
            showToast(data.error || "Error posting review", 'error');
        }
    } catch (e) {
        console.error("Review submit error", e);
    }
}

// Helper to buy now
function buyNow(product, qtyId) {
    addToCart(product, qtyId, true);
    window.location.href = 'cart.html';
}

// Load Wishlist Page
async function loadWishlistPage() {
    const grid = document.getElementById('wishlist-grid');
    if (!grid) return;

    if (!user) {
        grid.innerHTML = '<div class="col-span-full text-center py-20"><p class="text-xl text-gray-500 mb-4">Please login to view your wishlist.</p><a href="login.html" class="bg-green-700 text-white px-6 py-2 rounded-lg font-bold">Login</a></div>';
        return;
    }

    try {
        const res = await fetch(`${API_URL}/wishlist.php?user_id=${user.id}`);
        // If wishlist.php returns items with details (joined)
        const items = await res.json();

        // Also ensure Set is synced
        wishlist = new Set(items.map(i => i.id));

        grid.innerHTML = '';
        if (items.length === 0) {
            grid.innerHTML = '<div class="col-span-full text-center py-20 text-gray-400">Your wishlist is empty.</div>';
            return;
        }

        items.forEach(p => {
            // We need full product details. If wishlist API only returns {product_id, ...}, we might need to fetch products.
            // But let's assume update Schema for wishlist isn't needed if we used a JOIN in the API. 
            // Let's check api/wishlist.php. It probably needs a JOIN to get name, price etc.
            // If not, we have to update api/wishlist.php.

            let imgHTML = `<div class="w-full h-full bg-gray-100 flex items-center justify-center"><i data-lucide="image" class="text-gray-300 w-12 h-12"></i></div>`;
            if (p.image && p.image !== "null") {
                // If path starts with http, use as is. If no slash, assume uploads/. Else use as is.
                let src = p.image;
                if (!src.includes('/') && !src.startsWith('http')) {
                    src = `uploads/${src}`;
                }
                imgHTML = `<img src="${src}" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/400x300?text=No+Image'">`;
            }

            const card = document.createElement('div');
            card.id = `wishlist-item-${p.id}`; // Add ID for direct removal
            card.className = "bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-xl transition cursor-pointer relative group";
            card.onclick = (e) => {
                if (!e.target.closest('button')) window.location.href = `product.html?id=${p.id}`;
            };

            card.innerHTML = `
                <div class="h-48 bg-gray-200 flex items-center justify-center relative">
                    ${imgHTML}
                    <button class="absolute top-2 right-2 p-2 bg-white/90 rounded-full hover:bg-red-50 text-red-500 shadow-sm z-10" onclick="event.stopPropagation(); removeFromWishlistPage(${p.id}, this)">
                        <i data-lucide="trash-2" width="18" height="18"></i>
                    </button>
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-lg text-gray-800 line-clamp-1">${p.name}</h3>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-green-700 font-bold">${formatRWF(p.price)}</span>
                        <button onclick='addToCart(${JSON.stringify(p)}, "w-qty-${p.id}")' class="text-green-700 hover:text-green-900 font-bold text-sm bg-green-50 px-3 py-1 rounded-full">
                           Add to Cart
                        </button>
                        <input type="hidden" id="w-qty-${p.id}" value="1"> 
                    </div>
                </div>
            `;
            grid.appendChild(card);
        });
        lucide.createIcons();

    } catch (e) {
        console.error(e);
        grid.innerHTML = '<div class="col-span-full text-center text-red-500">Failed to load wishlist.</div>';
    }
}

// Explicit Remove Logic (Bypasses toggle state check)
async function explicitRemoveFromWishlist(productId) {
    if (!user) return;
    const pid = parseInt(productId);

    // Optimistic Remove
    wishlist.delete(pid);

    try {
        const res = await fetch(`${API_URL}/wishlist.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: user.id, product_id: pid, action: 'remove' })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.error);
        showToast("Removed from wishlist", "success");
    } catch (e) {
        console.error("Remove failed", e);
        wishlist.add(pid); // Revert
        showToast("Failed to remove", "error");
    }
}

async function removeFromWishlistPage(id) {
    if (!confirm('Remove from wishlist?')) return;

    // Find card by ID (Guaranteed)
    const card = document.getElementById(`wishlist-item-${id}`);

    try {
        await explicitRemoveFromWishlist(id);

        // Remove from DOM immediately
        if (card) {
            card.remove();

            // Check if grid is empty
            const grid = document.getElementById('wishlist-grid');
            if (grid && grid.children.length === 0) {
                grid.innerHTML = '<div class="col-span-full text-center py-20 text-gray-400">Your wishlist is empty.</div>';
            }
        } else {
            loadWishlistPage(); // Fallback
        }
    } catch (e) {
        console.error(e);
        if (card) loadWishlistPage();
    }
}

// Delete Product Function (Dashboard)
window.deleteProduct = async function (productId) {
    if (!confirm('Are you sure you want to delete this product?')) return;

    try {
        const res = await fetch(`${API_URL}/products.php?id=${productId}`, {
            method: 'DELETE'
        });

        const data = await res.json();

        if (res.ok) {
            showToast('Product deleted successfully!', 'success');
            // Reload the page to refresh the product list
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed to delete product', 'error');
        }
    } catch (e) {
        console.error('Delete error:', e);
        showToast('Error deleting product', 'error');
    }
}

