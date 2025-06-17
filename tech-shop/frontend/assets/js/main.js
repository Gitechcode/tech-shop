// Main JavaScript file for TechShop

// Global variables
let cartCount = 0
let wishlistCount = 0

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  initializeApp()
})

// Initialize application
function initializeApp() {
  // Update cart count on page load
  updateCartCount()

  // Initialize tooltips
  initializeTooltips()

  // Initialize lazy loading for images
  initializeLazyLoading()

  // Initialize search functionality
  initializeSearch()

  // Initialize product quick view
  initializeQuickView()

  // Initialize wishlist functionality
  initializeWishlist()

  // Initialize price range slider
  initializePriceRange()

  // Initialize product image gallery
  initializeImageGallery()

  // Initialize form validation
  initializeFormValidation()
}

// Cart functionality
function addToCart(productId, quantity = 1) {
  showLoader()

  fetch("/tech-shop/frontend/api/cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      action: "add",
      product_id: productId,
      quantity: quantity,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      hideLoader()
      if (data.success) {
        showToast("Product added to cart!", "success")
        updateCartCount()
        updateCartDropdown()
      } else {
        showToast(data.message || "Error adding product to cart", "error")
      }
    })
    .catch((error) => {
      hideLoader()
      console.error("Error:", error)
      showToast("Error adding product to cart", "error")
    })
}

function updateCartQuantity(productId, quantity) {
  if (quantity < 1) {
    removeFromCart(productId)
    return
  }

  fetch("/tech-shop/frontend/api/cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      action: "update",
      product_id: productId,
      quantity: quantity,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        updateCartCount()
        updateCartDropdown()
      } else {
        showToast(data.message || "Error updating quantity", "error")
      }
    })
}

function removeFromCart(productId) {
  if (confirm("Are you sure you want to remove this item from your cart?")) {
    fetch("/tech-shop/frontend/api/cart.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        action: "remove",
        product_id: productId,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showToast("Item removed from cart", "success")
          updateCartCount()
          updateCartDropdown()
          // Remove row if on cart page
          const row = document.querySelector(`tr[data-product-id="${productId}"]`)
          if (row) {
            row.remove()
          }
        } else {
          showToast(data.message || "Error removing item", "error")
        }
      })
  }
}

function updateCartCount() {
  fetch("/tech-shop/frontend/api/cart.php?action=count")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        cartCount = data.count
        const cartBadge = document.querySelector(".cart-badge")
        if (cartBadge) {
          if (cartCount > 0) {
            cartBadge.textContent = cartCount
            cartBadge.style.display = "inline"
          } else {
            cartBadge.style.display = "none"
          }
        }
      }
    })
}

function updateCartDropdown() {
  // Update cart dropdown if it exists
  const cartDropdown = document.querySelector("#cartDropdown")
  if (cartDropdown) {
    fetch("/tech-shop/frontend/api/cart.php?action=dropdown")
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          cartDropdown.innerHTML = data.html
        }
      })
  }
}

// Wishlist functionality
function addToWishlist(productId) {
  fetch("/tech-shop/frontend/api/wishlist.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      action: "add",
      product_id: productId,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showToast("Product added to wishlist!", "success")
        updateWishlistCount()
        // Update heart icon
        const heartIcon = document.querySelector(`[data-wishlist="${productId}"]`)
        if (heartIcon) {
          heartIcon.classList.add("text-danger")
          heartIcon.classList.remove("text-muted")
        }
      } else {
        showToast(data.message || "Error adding to wishlist", "error")
      }
    })
}

function removeFromWishlist(productId) {
  fetch("/tech-shop/frontend/api/wishlist.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      action: "remove",
      product_id: productId,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showToast("Product removed from wishlist", "success")
        updateWishlistCount()
        // Update heart icon
        const heartIcon = document.querySelector(`[data-wishlist="${productId}"]`)
        if (heartIcon) {
          heartIcon.classList.remove("text-danger")
          heartIcon.classList.add("text-muted")
        }
      } else {
        showToast(data.message || "Error removing from wishlist", "error")
      }
    })
}

function updateWishlistCount() {
  fetch("/tech-shop/frontend/api/wishlist.php?action=count")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        wishlistCount = data.count
        const wishlistBadge = document.querySelector(".wishlist-badge")
        if (wishlistBadge) {
          if (wishlistCount > 0) {
            wishlistBadge.textContent = wishlistCount
            wishlistBadge.style.display = "inline"
          } else {
            wishlistBadge.style.display = "none"
          }
        }
      }
    })
}

// Search functionality
function initializeSearch() {
  const searchInput = document.querySelector("#searchInput")
  const searchResults = document.querySelector("#searchResults")

  if (searchInput) {
    let searchTimeout

    searchInput.addEventListener("input", function () {
      clearTimeout(searchTimeout)
      const query = this.value.trim()

      if (query.length >= 2) {
        searchTimeout = setTimeout(() => {
          performSearch(query)
        }, 300)
      } else {
        hideSearchResults()
      }
    })

    // Hide results when clicking outside
    document.addEventListener("click", (e) => {
      if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        hideSearchResults()
      }
    })
  }
}

function performSearch(query) {
  fetch(`/tech-shop/frontend/api/search.php?q=${encodeURIComponent(query)}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displaySearchResults(data.results)
      }
    })
}

function displaySearchResults(results) {
  const searchResults = document.querySelector("#searchResults")
  if (!searchResults) return

  if (results.length === 0) {
    searchResults.innerHTML = '<div class="p-3 text-muted">No products found</div>'
  } else {
    let html = ""
    results.forEach((product) => {
      html += `
                <div class="search-result-item p-2 border-bottom">
                    <div class="d-flex align-items-center">
                        <img src="${product.image || "/placeholder.svg?height=40&width=40"}" 
                             alt="${product.name}" class="me-2" style="width: 40px; height: 40px; object-fit: cover;">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">${product.name}</h6>
                            <small class="text-muted">${formatPrice(product.price)}</small>
                        </div>
                    </div>
                </div>
            `
    })
    searchResults.innerHTML = html
  }

  searchResults.style.display = "block"
}

function hideSearchResults() {
  const searchResults = document.querySelector("#searchResults")
  if (searchResults) {
    searchResults.style.display = "none"
  }
}

// Product quick view
function initializeQuickView() {
  const quickViewButtons = document.querySelectorAll("[data-quick-view]")
  quickViewButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const productId = this.getAttribute("data-quick-view")
      showQuickView(productId)
    })
  })
}

function showQuickView(productId) {
  fetch(`/tech-shop/frontend/api/product.php?id=${productId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayQuickViewModal(data.product)
      }
    })
}

function displayQuickViewModal(product) {
  const modalHtml = `
        <div class="modal fade" id="quickViewModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${product.name}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <img src="${product.image || "/placeholder.svg?height=300&width=300"}" 
                                     alt="${product.name}" class="img-fluid">
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted">${product.short_description}</p>
                                <div class="mb-3">
                                    <span class="h4 text-primary">${formatPrice(product.sale_price || product.price)}</span>
                                    ${product.sale_price ? `<span class="text-muted text-decoration-line-through ms-2">${formatPrice(product.price)}</span>` : ""}
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Quantity:</label>
                                    <input type="number" class="form-control" id="quickViewQuantity" value="1" min="1" max="${product.stock}">
                                </div>
                                <button class="btn btn-primary" onclick="addToCartFromQuickView(${product.id})">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `

  // Remove existing modal
  const existingModal = document.querySelector("#quickViewModal")
  if (existingModal) {
    existingModal.remove()
  }

  // Add new modal
  document.body.insertAdjacentHTML("beforeend", modalHtml)

  // Show modal
  const modal = new bootstrap.Modal(document.querySelector("#quickViewModal"))
  modal.show()
}

function addToCartFromQuickView(productId) {
  const quantity = document.querySelector("#quickViewQuantity").value
  addToCart(productId, Number.parseInt(quantity))

  // Close modal
  const modal = bootstrap.Modal.getInstance(document.querySelector("#quickViewModal"))
  modal.hide()
}

// Price range slider
function initializePriceRange() {
  const priceRange = document.querySelector("#priceRange")
  if (priceRange) {
    // Initialize price range slider (you can use a library like noUiSlider)
    // For now, we'll use simple input handling
    const minPrice = document.querySelector("#minPrice")
    const maxPrice = document.querySelector("#maxPrice")

    if (minPrice && maxPrice) {
      minPrice.addEventListener("input", updatePriceRange)
      maxPrice.addEventListener("input", updatePriceRange)
    }
  }
}

function updatePriceRange() {
  const minPrice = document.querySelector("#minPrice").value
  const maxPrice = document.querySelector("#maxPrice").value

  // Update URL parameters
  const url = new URL(window.location)
  url.searchParams.set("min_price", minPrice)
  url.searchParams.set("max_price", maxPrice)

  // You can either update the page immediately or add a "Apply" button
  // For now, we'll just update the URL
  // window.location.href = url.toString();
}

// Image gallery
function initializeImageGallery() {
  const galleryThumbnails = document.querySelectorAll(".gallery-thumbnail")
  const mainImage = document.querySelector("#mainProductImage")

  galleryThumbnails.forEach((thumbnail) => {
    thumbnail.addEventListener("click", function () {
      const newSrc = this.getAttribute("data-image")
      if (mainImage && newSrc) {
        mainImage.src = newSrc

        // Update active thumbnail
        galleryThumbnails.forEach((thumb) => thumb.classList.remove("active"))
        this.classList.add("active")
      }
    })
  })
}

// Form validation
function initializeFormValidation() {
  const forms = document.querySelectorAll(".needs-validation")

  forms.forEach((form) => {
    form.addEventListener("submit", (event) => {
      if (!form.checkValidity()) {
        event.preventDefault()
        event.stopPropagation()
      }
      form.classList.add("was-validated")
    })
  })
}

// Tooltips
function initializeTooltips() {
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  tooltipTriggerList.map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl))
}

// Lazy loading
function initializeLazyLoading() {
  const images = document.querySelectorAll("img[data-src]")

  if ("IntersectionObserver" in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const img = entry.target
          img.src = img.dataset.src
          img.classList.remove("lazy")
          imageObserver.unobserve(img)
        }
      })
    })

    images.forEach((img) => imageObserver.observe(img))
  } else {
    // Fallback for older browsers
    images.forEach((img) => {
      img.src = img.dataset.src
      img.classList.remove("lazy")
    })
  }
}

// Wishlist initialization
function initializeWishlist() {
  const wishlistButtons = document.querySelectorAll("[data-wishlist]")

  wishlistButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const productId = this.getAttribute("data-wishlist")
      const isInWishlist = this.classList.contains("text-danger")

      if (isInWishlist) {
        removeFromWishlist(productId)
      } else {
        addToWishlist(productId)
      }
    })
  })
}

// Utility functions
function showToast(message, type = "info") {
  const toastContainer = getOrCreateToastContainer()

  const toastId = "toast-" + Date.now()
  const bgClass = type === "success" ? "bg-success" : type === "error" ? "bg-danger" : "bg-info"

  const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${type === "success" ? "check-circle" : type === "error" ? "exclamation-triangle" : "info-circle"} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `

  toastContainer.insertAdjacentHTML("beforeend", toastHtml)

  const toastElement = document.getElementById(toastId)
  const toast = new bootstrap.Toast(toastElement, {
    autohide: true,
    delay: 5000,
  })

  toast.show()

  // Remove toast element after it's hidden
  toastElement.addEventListener("hidden.bs.toast", () => {
    toastElement.remove()
  })
}

function getOrCreateToastContainer() {
  let container = document.querySelector(".toast-container")
  if (!container) {
    container = document.createElement("div")
    container.className = "toast-container position-fixed top-0 end-0 p-3"
    container.style.zIndex = "1055"
    document.body.appendChild(container)
  }
  return container
}

function showLoader() {
  let loader = document.querySelector("#globalLoader")
  if (!loader) {
    loader = document.createElement("div")
    loader.id = "globalLoader"
    loader.className = "position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
    loader.style.backgroundColor = "rgba(0,0,0,0.5)"
    loader.style.zIndex = "9999"
    loader.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        `
    document.body.appendChild(loader)
  }
  loader.style.display = "flex"
}

function hideLoader() {
  const loader = document.querySelector("#globalLoader")
  if (loader) {
    loader.style.display = "none"
  }
}

function formatPrice(price) {
  return "$" + Number.parseFloat(price).toFixed(2)
}

function debounce(func, wait) {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

// Product comparison
function addToCompare(productId) {
  const compareList = JSON.parse(localStorage.getItem("compareList") || "[]")

  if (compareList.length >= 4) {
    showToast("You can compare maximum 4 products", "error")
    return
  }

  if (compareList.includes(productId)) {
    showToast("Product already in comparison list", "error")
    return
  }

  compareList.push(productId)
  localStorage.setItem("compareList", JSON.stringify(compareList))

  showToast("Product added to comparison", "success")
  updateCompareCount()
}

function removeFromCompare(productId) {
  let compareList = JSON.parse(localStorage.getItem("compareList") || "[]")
  compareList = compareList.filter((id) => id !== productId)
  localStorage.setItem("compareList", JSON.stringify(compareList))

  showToast("Product removed from comparison", "success")
  updateCompareCount()
}

function updateCompareCount() {
  const compareList = JSON.parse(localStorage.getItem("compareList") || "[]")
  const compareBadge = document.querySelector(".compare-badge")

  if (compareBadge) {
    if (compareList.length > 0) {
      compareBadge.textContent = compareList.length
      compareBadge.style.display = "inline"
    } else {
      compareBadge.style.display = "none"
    }
  }
}

// Newsletter subscription
function subscribeNewsletter(email) {
  fetch("/tech-shop/frontend/api/newsletter.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      action: "subscribe",
      email: email,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showToast("Successfully subscribed to newsletter!", "success")
      } else {
        showToast(data.message || "Error subscribing to newsletter", "error")
      }
    })
}

// Product rating
function submitRating(productId, rating, title, comment) {
  fetch("/tech-shop/frontend/api/review.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      action: "submit",
      product_id: productId,
      rating: rating,
      title: title,
      comment: comment,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showToast("Review submitted successfully!", "success")
      } else {
        showToast(data.message || "Error submitting review", "error")
      }
    })
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", () => {
  updateCompareCount()
})
