<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Image Manager</title>
    <link rel="icon" href="./img/mylogo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        #upload-dropzone.dragover {
            border-color: #6366f1;
            background-color: #eef2ff;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-100 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <header class="bg-white p-6 rounded-xl shadow mb-8 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">📸 Image Manager</h1>
            <span class="text-sm text-gray-500">Powered by PHP/MySQL</span>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Category Manager -->
            <div class="bg-white p-6 rounded-xl shadow">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Category Manager</h2>

                <!-- Add Subcategory -->
                <form id="add-subcategory-form" class="space-y-3 mb-6">
                    <select id="subcategory-year-select" required
                        class="w-full p-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="" disabled selected>Select a Year</option>
                    </select>
                    <input type="text" id="new-subcategory-name" placeholder="New Subcategory Name" required
                        class="w-full p-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <button type="submit"
                        class="w-full bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700 transition">
                        ➕ Add Subcategory
                    </button>
                    <p id="category-message" class="hidden text-sm mt-2"></p>
                </form>

                <!-- Existing Categories -->
                <h3 class="text-md font-semibold text-gray-700 mb-2">Existing Categories</h3>
                <ul id="categories-list"
                    class="space-y-2 max-h-72 overflow-y-auto border rounded-md p-2 bg-gray-50 text-sm">
                    <li class="text-gray-500 italic">Loading...</li>
                </ul>
            </div>

            <!-- Uploader + Gallery -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Image Uploader -->
                <div class="bg-white p-6 rounded-xl shadow">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Upload Images</h2>
                    <form id="upload-form" class="space-y-4">
                        <select id="year-select" required
                            class="w-full p-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="" disabled selected>Select Year</option>
                        </select>
                        <select id="subcategory-select" required disabled
                            class="w-full p-2 border border-gray-300 rounded-md bg-gray-100">
                            <option value="" disabled selected>Select a Year First</option>
                        </select>

                        <!-- Drag & Drop Zone -->
                        <div id="upload-dropzone"
                            class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer transition">
                            <p class="text-gray-500">Drag & Drop an image here or click to browse</p>
                            <input type="file" id="image-file" accept="image/*" class="hidden">
                        </div>

                        <button type="submit" id="upload-button"
                            class="w-full bg-green-600 text-white py-2 rounded-md hover:bg-green-700 transition disabled:opacity-50"
                            disabled>
                            ⬆ Upload Image
                        </button>

                        <progress id="upload-progress" value="0" max="100" class="w-full h-2 hidden"></progress>
                        <p id="upload-message" class="hidden text-sm mt-2"></p>
                    </form>
                </div>

                <!-- Image Gallery -->
                <div class="bg-white p-6 rounded-xl shadow">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Gallery</h2>
                    <div id="uploaded-images-container"
                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <p id="empty-gallery-message" class="col-span-full text-gray-500 italic">No images uploaded yet.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-modal"
        class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Confirm Deletion</h3>
            <p class="text-gray-600 mb-6">Are you sure you want to delete this item? This action cannot be undone.</p>
            <div class="flex justify-end gap-3">
                <button id="cancel-delete"
                    class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300">Cancel</button>
                <button id="confirm-delete"
                    class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700">Delete</button>
            </div>
        </div>
    </div>

    <script>
        const API_URL = "api.php";

        const categoryYearSelect = document.getElementById("subcategory-year-select");
        const categoryInput = document.getElementById("new-subcategory-name");
        const categoryMessage = document.getElementById("category-message");
        const categoriesList = document.getElementById("categories-list");

        const yearSelect = document.getElementById("year-select");
        const subcategorySelect = document.getElementById("subcategory-select");
        const uploadButton = document.getElementById("upload-button");
        const uploadForm = document.getElementById("upload-form");
        const imageFile = document.getElementById("image-file");
        const uploadDropzone = document.getElementById("upload-dropzone");
        const uploadProgress = document.getElementById("upload-progress");
        const uploadMessage = document.getElementById("upload-message");
        const imagesContainer = document.getElementById("uploaded-images-container");
        const emptyGalleryMessage = document.getElementById("empty-gallery-message");

        const deleteModal = document.getElementById("delete-modal");
        const cancelDelete = document.getElementById("cancel-delete");
        const confirmDelete = document.getElementById("confirm-delete");

        let categoriesData = [];
        let deleteTarget = {
            type: null,
            id: null
        };

        async function apiFetch(action, method = "GET", body = null) {
            const url = `${API_URL}?action=${action}`;
            const options = {
                method
            };
            if (method === "POST" && body) {
                if (body instanceof FormData) {
                    options.body = body;
                } else {
                    options.headers = {
                        "Content-Type": "application/json"
                    };
                    options.body = JSON.stringify(body);
                }
            }
            const response = await fetch(url, options);
            const data = await response.json();
            if (!response.ok || data.success === false) throw new Error(data.message || "API failed");
            return data;
        }

        function displayMessage(element, message, type) {
            element.textContent = message;
            element.className = `mt-2 text-sm px-3 py-2 rounded-md ${type}`;
            element.classList.remove("hidden");
            setTimeout(() => element.classList.add("hidden"), 4000);
        }

        async function loadCategories() {
            try {
                const res = await apiFetch("get_categories");
                categoriesData = res.data;
                [yearSelect, categoryYearSelect].forEach(sel => {
                    sel.innerHTML = `<option disabled selected>Select a Year</option>`;
                    categoriesData.forEach(y => {
                        const opt = document.createElement("option");
                        opt.value = y.id;
                        opt.textContent = y.year;
                        sel.appendChild(opt);
                    });
                });

                categoriesList.innerHTML = "";
                if (!categoriesData.length) {
                    categoriesList.innerHTML = "<li>No categories found.</li>";
                } else {
                    categoriesData.forEach(y => {
                        const li = document.createElement("li");
                        li.innerHTML = `
              <p class="font-semibold flex justify-between">
                ${y.year}
                <button data-id="${y.id}" class="delete-cat px-2 py-0.5 bg-red-100 text-red-600 rounded text-xs">Delete</button>
              </p>
              <div class="mt-1">${y.subcategories.map(s =>
                `<span class="px-2 py-0.5 text-xs bg-indigo-100 text-indigo-700 rounded-full mr-1">${s.name}</span>`).join("")}</div>`;
                        categoriesList.appendChild(li);
                    });
                }
                loadImages();
            } catch (err) {
                categoriesList.innerHTML = `<li class="text-red-600">${err.message}</li>`;
            }
        }

        uploadForm.addEventListener("submit", async e => {
            e.preventDefault();
            const file = imageFile.files[0];
            const subId = subcategorySelect.value;
            if (!file || !subId) return;
            uploadButton.disabled = true;
            uploadProgress.classList.remove("hidden");
            const formData = new FormData();
            formData.append("image_file", file);
            formData.append("subcategory_id", subId);
            try {
                const res = await apiFetch("upload_image", "POST", formData);
                displayMessage(uploadMessage, res.message, "bg-green-100 text-green-700");
                uploadForm.reset();
                uploadProgress.classList.add("hidden");
                loadImages();
            } catch (err) {
                displayMessage(uploadMessage, err.message, "bg-red-100 text-red-700");
            } finally {
                uploadButton.disabled = false;
            }
        });

        async function loadImages() {
            try {
                const res = await apiFetch("get_images");
                const items = res.data.items;
                imagesContainer.innerHTML = "";
                if (!items.length) {
                    emptyGalleryMessage.classList.remove("hidden");
                    return;
                }
                emptyGalleryMessage.classList.add("hidden");
                items.forEach(img => {
                    const card = document.createElement("div");
                    card.className = "bg-white rounded-xl shadow hover:shadow-lg transition border overflow-hidden";
                    card.innerHTML = `
            <img src="${img.url}" alt="${img.filename}" class="w-full h-40 object-cover">
            <div class="p-3">
              <p class="text-sm font-semibold truncate">${img.filename}</p>
              <span class="text-xs px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded">${img.category_name} (${img.year_value})</span>
              <div class="flex justify-end mt-2">
                <button data-id="${img.id}" class="delete-img px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200">Delete</button>
              </div>
            </div>`;
                    imagesContainer.appendChild(card);
                });
            } catch (err) {
                imagesContainer.innerHTML = `<p class="text-red-600">${err.message}</p>`;
            }
        }

        imagesContainer.addEventListener("click", e => {
            if (e.target.closest(".delete-img")) {
                deleteTarget = {
                    type: "image",
                    id: e.target.dataset.id
                };
                deleteModal.classList.remove("hidden");
            }
        });
        categoriesList.addEventListener("click", e => {
            if (e.target.closest(".delete-cat")) {
                deleteTarget = {
                    type: "category",
                    id: e.target.dataset.id
                };
                deleteModal.classList.remove("hidden");
            }
        });

        cancelDelete.addEventListener("click", () => deleteModal.classList.add("hidden"));
        confirmDelete.addEventListener("click", async () => {
            try {
                const res = await apiFetch(`delete_${deleteTarget.type}`, "POST", {
                    id: deleteTarget.id
                });
                displayMessage(uploadMessage, res.message, "bg-orange-100 text-orange-700");
                loadCategories();
            } catch (err) {
                displayMessage(uploadMessage, err.message, "bg-red-100 text-red-700");
            } finally {
                deleteModal.classList.add("hidden");
                deleteTarget = {
                    type: null,
                    id: null
                };
            }
        });

        document.getElementById("add-subcategory-form").addEventListener("submit", async e => {
            e.preventDefault();
            try {
                const res = await apiFetch("add_subcategory", "POST", {
                    year_id: categoryYearSelect.value,
                    name: categoryInput.value
                });
                displayMessage(categoryMessage, res.message, "bg-green-100 text-green-700");
                loadCategories();
            } catch (err) {
                displayMessage(categoryMessage, err.message, "bg-red-100 text-red-700");
            }
        });

        uploadDropzone.addEventListener("click", () => imageFile.click());
        uploadDropzone.addEventListener("dragover", e => {
            e.preventDefault();
            uploadDropzone.classList.add("dragover");
        });
        uploadDropzone.addEventListener("dragleave", () => uploadDropzone.classList.remove("dragover"));
        uploadDropzone.addEventListener("drop", e => {
            e.preventDefault();
            uploadDropzone.classList.remove("dragover");
            imageFile.files = e.dataTransfer.files;
        });

        yearSelect.addEventListener("change", e => {
            subcategorySelect.innerHTML = `<option disabled selected>Select Category</option>`;
            subcategorySelect.disabled = true;
            const year = categoriesData.find(y => y.id == e.target.value);
            if (year && year.subcategories.length > 0) {
                year.subcategories.forEach(sub => {
                    const opt = document.createElement("option");
                    opt.value = sub.id;
                    opt.textContent = sub.name;
                    subcategorySelect.appendChild(opt);
                });
                subcategorySelect.disabled = false;
                uploadButton.disabled = false;
            }
        });

        window.onload = loadCategories;
    </script>
</body>

</html>