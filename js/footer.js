// Footer Injection
document.addEventListener("DOMContentLoaded", () => {
    const footerHTML = `
    <footer class="bg-gray-900 text-white mt-auto">
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Brand -->
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="bg-white p-1 rounded-full"><i data-lucide="sprout" class="text-green-700 w-5 h-5"></i></div>
                        <span class="font-bold text-xl">AgriShop</span>
                    </div>
                    <p class="text-gray-400 text-sm mb-4">Empowering Rwandan farmers through technology and direct market access.</p>
                    <div class="flex gap-4">
                        <a href="#" class="text-gray-400 hover:text-white"><i data-lucide="facebook" width="20"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i data-lucide="twitter" width="20"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i data-lucide="instagram" width="20"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="font-bold mb-4 text-green-500">Quick Links</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="index.html" class="hover:text-white transition">Home</a></li>
                        <li><a href="market.html" class="hover:text-white transition">Marketplace</a></li>
                        <li><a href="advisory.html" class="hover:text-white transition">Advisory</a></li>
                        <li><a href="prices.html" class="hover:text-white transition">Market Prices</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h4 class="font-bold mb-4 text-green-500">Support</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="#" class="hover:text-white transition">Help Center</a></li>
                        <li><a href="#" class="hover:text-white transition">Farmer Resources</a></li>
                        <li><a href="#" class="hover:text-white transition">Terms of Service</a></li>
                        <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div>
                    <h4 class="font-bold mb-4 text-green-500">Newsletter</h4>
                    <p class="text-gray-400 text-sm mb-4">Subscribe for daily market updates.</p>
                    <form class="flex gap-2" onsubmit="event.preventDefault(); alert('Subscribed!')">
                        <input type="email" placeholder="Email address" class="bg-gray-800 text-white px-3 py-2 rounded-lg text-sm w-full outline-none focus:ring-1 focus:ring-green-500">
                        <button class="bg-green-600 px-3 py-2 rounded-lg hover:bg-green-700 transition"><i data-lucide="send" width="16"></i></button>
                    </form>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-12 pt-8 text-center md:text-left flex flex-col md:flex-row justify-between items-center text-xs text-gray-500">
                <p>&copy; 2023 AgriShop Rwanda. All rights reserved.</p>
                <div class="flex gap-4 mt-4 md:mt-0">
                    <span>USSD: *999#</span>
                    <span>Support: +250 788 000 000</span>
                </div>
            </div>
        </div>
    </footer>
    `;

    // Append to body (it will sit at bottom due to flex-col min-h-screen existing in body)
    document.body.insertAdjacentHTML('beforeend', footerHTML);

    // Re-initialize icons for the new footer
    if (window.lucide) {
        window.lucide.createIcons();
    }
});
