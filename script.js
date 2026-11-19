document.addEventListener("DOMContentLoaded", () => {
    
    // 1. Dynamic Greeting (Dynamic Username Fetch)
    const greetingElement = document.getElementById("dynamic-greeting");
    if (greetingElement) {
        const currentHour = new Date().getHours();
        let greeting = "Good evening";
        
        if (currentHour < 12) {
            greeting = "Good morning";
        } else if (currentHour < 18) {
            greeting = "Good afternoon";
        }
        
        // Read logged in name from HTML data-username attribute
        const username = greetingElement.getAttribute("data-username") || "Student";
        greetingElement.innerHTML = `${greeting} 👋<br><span class="user-name">${username}!</span>`;
    }

    // 2. Sidebar Active Menu Switcher 
    const menuItems = document.querySelectorAll(".nav-menu li");
    menuItems.forEach(item => {
        item.addEventListener("click", function() {
            menuItems.forEach(li => li.classList.remove("active"));
            this.classList.add("active");
        });
    });

    // 3. Sidebar Toggle 
    const menuToggleBtn = document.getElementById("menu-toggle");
    const sidebar = document.querySelector(".sidebar");
    
    if (menuToggleBtn && sidebar) {
        menuToggleBtn.addEventListener("click", () => {
            sidebar.classList.toggle("collapsed");
        });
    }
});

// Page Loader / Preloader Fade Out
window.addEventListener("load", function () {
    const loader = document.getElementById("page-loader") || document.getElementById("preloader");

    if (!loader) return;

    setTimeout(() => {
        loader.style.opacity = "0";
        setTimeout(() => {
            loader.remove();
        }, 500);
    }, 400);
});