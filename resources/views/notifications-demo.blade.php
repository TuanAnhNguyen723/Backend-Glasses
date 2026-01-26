<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Notifications Demo - SpecShop Admin</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#3994ef",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101922",
                    },
                    fontFamily: {
                        "display": ["Inter"]
                    },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display min-h-screen overflow-x-hidden relative">
    
    <!-- Success Toast (Top Right) -->
    <div class="fixed top-6 right-6 z-[100] animate-in fade-in slide-in-from-top-4 duration-300">
        <div class="flex items-center gap-4 bg-[#e6f4ea] border border-[#34a853]/20 px-5 py-4 rounded-xl shadow-xl min-w-[320px]">
            <div class="flex items-center justify-center bg-[#34a853] text-white rounded-full size-8 shrink-0">
                <span class="material-symbols-outlined text-lg font-bold">check</span>
            </div>
            <div class="flex flex-col grow">
                <p class="text-[#0d652d] text-sm font-semibold leading-none">Success</p>
                <p class="text-[#0d652d] text-sm font-medium mt-1">Product updated successfully</p>
            </div>
            <button class="text-[#0d652d] hover:bg-[#34a853]/10 p-1 rounded-full transition-colors">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>
    </div>

    <!-- Error Toast (Top Right - Below Success) -->
    <div class="fixed top-28 right-6 z-[100] animate-in fade-in slide-in-from-top-4 duration-300">
        <div class="flex items-center gap-4 bg-[#fef2f2] border border-[#ef4444]/20 px-5 py-4 rounded-xl shadow-xl min-w-[320px]">
            <div class="flex items-center justify-center bg-[#ef4444] text-white rounded-full size-8 shrink-0">
                <span class="material-symbols-outlined text-lg font-bold">error</span>
            </div>
            <div class="flex flex-col grow">
                <p class="text-[#991b1b] text-sm font-semibold leading-none">Error</p>
                <p class="text-[#991b1b] text-sm font-medium mt-1">Failed to delete product</p>
            </div>
            <button class="text-[#991b1b] hover:bg-[#ef4444]/10 p-1 rounded-full transition-colors">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>
    </div>

    <!-- Warning Toast (Top Right - Below Error) -->
    <div class="fixed top-50 right-6 z-[100] animate-in fade-in slide-in-from-top-4 duration-300">
        <div class="flex items-center gap-4 bg-[#fffbeb] border border-[#f59e0b]/20 px-5 py-4 rounded-xl shadow-xl min-w-[320px]">
            <div class="flex items-center justify-center bg-[#f59e0b] text-white rounded-full size-8 shrink-0">
                <span class="material-symbols-outlined text-lg font-bold">warning</span>
            </div>
            <div class="flex flex-col grow">
                <p class="text-[#92400e] text-sm font-semibold leading-none">Warning</p>
                <p class="text-[#92400e] text-sm font-medium mt-1">Low stock: Only 5 units remaining</p>
            </div>
            <button class="text-[#92400e] hover:bg-[#f59e0b]/10 p-1 rounded-full transition-colors">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>
    </div>

    <!-- Info Toast (Top Right - Below Warning) -->
    <div class="fixed top-72 right-6 z-[100] animate-in fade-in slide-in-from-top-4 duration-300">
        <div class="flex items-center gap-4 bg-[#eff6ff] border border-[#3b82f6]/20 px-5 py-4 rounded-xl shadow-xl min-w-[320px]">
            <div class="flex items-center justify-center bg-[#3b82f6] text-white rounded-full size-8 shrink-0">
                <span class="material-symbols-outlined text-lg font-bold">info</span>
            </div>
            <div class="flex flex-col grow">
                <p class="text-[#1e40af] text-sm font-semibold leading-none">Info</p>
                <p class="text-[#1e40af] text-sm font-medium mt-1">New order received from customer</p>
            </div>
            <button class="text-[#1e40af] hover:bg-[#3b82f6]/10 p-1 rounded-full transition-colors">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>
    </div>

    <!-- Main Content Wrapper (Blurred when notification is active) -->
    <div class="layout-container flex h-full grow flex-col blur-[2px] transition-all duration-300">
        <!-- Top Navigation -->
        <header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-slate-200 dark:border-slate-800 bg-white dark:bg-background-dark px-10 py-3 sticky top-0 z-50">
            <div class="flex items-center gap-8">
                <div class="flex items-center gap-4 text-slate-900 dark:text-white">
                    <div class="size-6 text-primary">
                        <span class="material-symbols-outlined text-3xl">view_in_ar</span>
                    </div>
                    <h2 class="text-slate-900 dark:text-white text-lg font-bold leading-tight tracking-tight">SpecShop</h2>
                </div>
                <label class="flex flex-col min-w-40 !h-10 max-w-64">
                    <div class="flex w-full flex-1 items-stretch rounded-lg h-full">
                        <div class="text-slate-500 flex border-none bg-slate-100 dark:bg-slate-800 items-center justify-center pl-4 rounded-l-lg" data-icon="search">
                            <span class="material-symbols-outlined">search</span>
                        </div>
                        <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-slate-900 dark:text-white focus:outline-0 focus:ring-0 border-none bg-slate-100 dark:bg-slate-800 placeholder:text-slate-500 px-4 rounded-l-none pl-2 text-sm font-normal" placeholder="Search orders, frames..." value=""/>
                    </div>
                </label>
            </div>
            <div class="flex flex-1 justify-end gap-6">
                <div class="flex gap-2">
                    <button class="flex items-center justify-center rounded-lg size-10 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                        <span class="material-symbols-outlined">notifications</span>
                    </button>
                    <button class="flex items-center justify-center rounded-lg size-10 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                        <span class="material-symbols-outlined">settings</span>
                    </button>
                </div>
                <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10 border-2 border-primary" data-alt="Admin user profile picture" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuCLgLRgs9HiQjt3mKeYV_qc8ip9HjLG284TxQYL4e7k4Mqv7cDJTjis3vB6n5n-vSmToNE5dA7GFLGXySc31wX8lXZ4xufrhwMOPVcbqxQiOCnPsxbLbukUA_WP_JYpAq_pDEXQ3lwMOiTUjWg6Md_Ckfrd787WtxbLs1lvA19yPnS5HZaIiYNQZmR9cB5Xn69vCKGaTwnfPP-C4IqHk98cTHdaFp1HXGUcF1u9AUm219tgoZGg8eQMogltw0dwCZm4y2_fR_7lLg");'></div>
            </div>
        </header>
        <div class="flex flex-1">
            <!-- Sidebar -->
            <aside class="w-64 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-background-dark hidden lg:flex flex-col p-4 gap-6">
                <div class="flex flex-col gap-1">
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-wider px-3 mb-2">Management</h3>
                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer text-slate-600 dark:text-slate-400">
                        <span class="material-symbols-outlined">dashboard</span>
                        <p class="text-sm font-medium">Dashboard</p>
                    </div>
                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary/10 text-primary cursor-pointer">
                        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1">eyeglasses</span>
                        <p class="text-sm font-bold">Products</p>
                    </div>
                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer text-slate-600 dark:text-slate-400">
                        <span class="material-symbols-outlined">shopping_cart</span>
                        <p class="text-sm font-medium">Orders</p>
                    </div>
                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer text-slate-600 dark:text-slate-400">
                        <span class="material-symbols-outlined">group</span>
                        <p class="text-sm font-medium">Customers</p>
                    </div>
                </div>
            </aside>
            <!-- Main Page Content -->
            <main class="flex-1 flex flex-col p-8 gap-6 max-w-5xl mx-auto w-full">
                <!-- Breadcrumbs -->
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <a class="text-slate-500 hover:text-primary transition-colors font-medium" href="#">Dashboard</a>
                    <span class="text-slate-300">/</span>
                    <a class="text-slate-500 hover:text-primary transition-colors font-medium" href="#">Products</a>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-900 dark:text-white font-bold">Edit Classic Aviator Gold</span>
                </div>
                <!-- Page Heading -->
                <div class="flex flex-wrap justify-between items-end gap-3">
                    <div class="flex flex-col gap-1">
                        <h1 class="text-slate-900 dark:text-white text-3xl font-black leading-tight tracking-tight">Notifications Demo</h1>
                        <p class="text-slate-500 text-base font-normal">Xem các loại thông báo khác nhau</p>
                    </div>
                    <div class="flex gap-3">
                        <button class="flex items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-bold transition-all hover:bg-slate-300">
                            <span class="truncate">Preview on Store</span>
                        </button>
                        <button class="flex items-center justify-center overflow-hidden rounded-lg h-10 px-6 bg-primary text-white text-sm font-bold shadow-lg shadow-primary/30 transition-all hover:scale-[1.02] active:scale-95">
                            <span class="truncate">Save Changes</span>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column: Media -->
                    <div class="lg:col-span-1 flex flex-col gap-6">
                        <div class="p-4 rounded-xl shadow-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                            <p class="text-slate-900 dark:text-white font-bold mb-4">Product Image</p>
                            <div class="w-full bg-center bg-no-repeat aspect-square bg-cover rounded-lg border border-slate-100 dark:border-slate-800 mb-4" data-alt="Detailed view of golden aviator frame" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuB88lMJ8vKQv7s6xc-xEyewEiRapqHyOhy0DhWcqO8q6XWZ6i_M8jbVZFASKcFJVr-2XsHqnCWybOfInNhv3jTrVFAB-VBpUb89YVCPQDW2ADmQr9QXgW4U31iiCekfBH9Z5imYeKUBcQu3t5NJ5-cj6UsxZQKtzlLw3vWEbUtAC0fPvL3J2ELMHv6BJz458LHiQbitKold-iNHSY__q85mKe67ytZWbS4wO36v-6q28hp7fhyAgCb1fnEpXO55brcU4a1wdbtb_g");'></div>
                            <button class="w-full flex items-center justify-center gap-2 overflow-hidden rounded-lg h-10 bg-primary/10 text-primary text-sm font-bold hover:bg-primary/20 transition-colors">
                                <span class="material-symbols-outlined text-lg">image</span>
                                <span class="truncate">Change Image</span>
                            </button>
                        </div>
                        <div class="p-4 rounded-xl shadow-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                            <p class="text-slate-900 dark:text-white font-bold mb-4">Inventory</p>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center p-3 bg-slate-50 dark:bg-slate-800 rounded-lg">
                                    <span class="text-sm text-slate-500">In Stock</span>
                                    <span class="text-sm font-bold text-slate-900 dark:text-white">124 units</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-slate-50 dark:bg-slate-800 rounded-lg">
                                    <span class="text-sm text-slate-500">SKU</span>
                                    <span class="text-sm font-bold text-slate-900 dark:text-white">AVG-2024-001</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Right Column: Details -->
                    <div class="lg:col-span-2 flex flex-col gap-6">
                        <div class="p-6 rounded-xl shadow-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col gap-6">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Product Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Frame Name</label>
                                    <input class="w-full rounded-lg border-slate-200 dark:border-slate-800 dark:bg-slate-950 text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary" type="text" value="Classic Aviator Gold"/>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Price ($)</label>
                                    <input class="w-full rounded-lg border-slate-200 dark:border-slate-800 dark:bg-slate-950 text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary" type="number" value="189.00"/>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Material</label>
                                <select class="w-full rounded-lg border-slate-200 dark:border-slate-800 dark:bg-slate-950 text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary">
                                    <option>Titanium Alloy</option>
                                    <option>Stainless Steel</option>
                                    <option>Acetate</option>
                                </select>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Description</label>
                                <textarea class="w-full rounded-lg border-slate-200 dark:border-slate-800 dark:bg-slate-950 text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary" rows="4">Timeless aviator design featuring premium gold-plated titanium frames. Lightweight and durable with adjustable nose pads for all-day comfort.</textarea>
                            </div>
                            <div class="flex items-center gap-4 py-2">
                                <div class="flex items-center h-5">
                                    <input checked="" class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary" type="checkbox"/>
                                </div>
                                <div class="text-sm">
                                    <label class="font-medium text-slate-700 dark:text-slate-300">Featured Product</label>
                                    <p class="text-slate-500">Show this product in the homepage hero slider.</p>
                                </div>
                            </div>
                        </div>
                        <!-- Secondary Actions -->
                        <div class="flex justify-end gap-3">
                            <button class="text-red-500 hover:text-red-600 text-sm font-bold px-4 py-2">Delete Product</button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <!-- Overlay to simulate the context of the toast focus -->
    <div class="fixed inset-0 bg-slate-900/10 pointer-events-none z-[90]"></div>
</body>
</html>
