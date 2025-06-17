document.addEventListener("DOMContentLoaded", () => {
  // FAQ Search Functionality
  const searchInput = document.getElementById("faq-search")
  const faqCards = document.querySelectorAll(".accordion .card")

  searchInput.addEventListener("keyup", () => {
    const searchTerm = searchInput.value.toLowerCase()

    faqCards.forEach((card) => {
      const question = card.querySelector(".btn-link").textContent.toLowerCase()
      const answer = card.querySelector(".card-body").textContent.toLowerCase()

      if (question.includes(searchTerm) || answer.includes(searchTerm)) {
        card.style.display = ""
      } else {
        card.style.display = "none"
      }
    })

    // Show/hide section titles based on visible cards
    document.querySelectorAll(".faq-section").forEach((section) => {
      const visibleCards = Array.from(section.querySelectorAll(".card")).filter((card) => {
        return card.style.display !== "none"
      })

      if (visibleCards.length > 0) {
        section.querySelector(".section-title").style.display = ""
      } else {
        section.querySelector(".section-title").style.display = "none"
      }
    })

    // Show message if no results
    const noResults = document.getElementById("no-results")
    const hasVisibleCards = Array.from(faqCards).some((card) => card.style.display !== "none")

    if (!hasVisibleCards && searchTerm) {
      if (!noResults) {
        const message = document.createElement("div")
        message.id = "no-results"
        message.className = "alert alert-info mt-3"
        message.textContent = "No matching questions found. Please try a different search term."
        document.querySelector(".accordion").after(message)
      }
    } else if (noResults) {
      noResults.remove()
    }
  })

  // Category Filtering
  const categoryLinks = document.querySelectorAll(".faq-categories .nav-link")

  categoryLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault()

      // Update active state
      categoryLinks.forEach((l) => l.classList.remove("active"))
      this.classList.add("active")

      const category = this.getAttribute("data-category")

      // Reset search
      searchInput.value = ""

      // Show/hide sections based on category
      if (category === "all") {
        document.querySelectorAll(".faq-section").forEach((section) => {
          section.style.display = ""
        })
        faqCards.forEach((card) => {
          card.style.display = ""
        })
      } else {
        document.querySelectorAll(".faq-section").forEach((section) => {
          if (section.getAttribute("data-category") === category) {
            section.style.display = ""
          } else {
            section.style.display = "none"
          }
        })
      }

      // Remove no results message if it exists
      const noResults = document.getElementById("no-results")
      if (noResults) {
        noResults.remove()
      }
    })
  })

  // Expand/collapse all functionality
  const expandAllBtn = document.getElementById("expand-all")
  const collapseAllBtn = document.getElementById("collapse-all")

  if (expandAllBtn) {
    expandAllBtn.addEventListener("click", () => {
      document.querySelectorAll(".collapse").forEach((item) => {
        item.classList.add("show")
      })
    })
  }

  if (collapseAllBtn) {
    collapseAllBtn.addEventListener("click", () => {
      document.querySelectorAll(".collapse.show").forEach((item) => {
        item.classList.remove("show")
      })
    })
  }

  // Track FAQ interactions for analytics
  document.querySelectorAll(".btn-link").forEach((button) => {
    button.addEventListener("click", function () {
      const question = this.textContent.trim()
      // This would typically send data to an analytics service
      console.log("FAQ interaction:", question)

      // You could implement actual tracking here:
      // trackEvent('FAQ', 'Question Clicked', question);
    })
  })
})
