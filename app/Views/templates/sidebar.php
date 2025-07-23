<!-- Sidebar Template: app/Views/templates/sidebar.php -->
<style>
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 220px;
        background: #222e3c;
        color: #fff;
        transition: width 0.3s;
        overflow-x: hidden;
        z-index: 1000;
    }

    .sidebar.minimized {
        width: 60px;
    }

    .sidebar .toggle-btn {
        position: absolute;
        top: 15px;
        right: -20px;
        background: #222e3c;
        border: none;
        color: #fff;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sidebar ul {
        list-style: none;
        padding: 60px 0 0 0;
        margin: 0;
    }

    .sidebar ul li {
        padding: 15px 20px;
        white-space: nowrap;
        transition: padding 0.3s;
    }

    .sidebar.minimized ul li {
        padding: 15px 10px;
        text-align: center;
    }

    .sidebar ul li a {
        color: #fff;
        text-decoration: none;
        display: block;
    }

    .sidebar .logo {
        padding: 20px;
        font-size: 1.4em;
        font-weight: bold;
        letter-spacing: 1px;
        text-align: center;
        background: #1a2230;
        transition: padding 0.3s, font-size 0.3s;
    }

    .sidebar.minimized .logo {
        padding: 20px 0;
        font-size: 1em;
    }

    @media (max-width: 768px) {
        .sidebar {
            width: 60px;
        }

        .sidebar:not(.minimized) {
            width: 180px;
        }
    }
</style>

<aside class="sidebar" id="sidebar">
    <div class="logo">
        <span id="sidebar-logo-text">PetPal</span>
    </div>
    <button class="toggle-btn" id="sidebar-toggle" aria-label="Toggle sidebar">
        <span id="toggle-icon">&#9776;</span>
    </button>

    <ul>
        <li><a href="<?= site_url('dashboard') ?>"><span class="icon">&#128062;</span> <span class="sidebar-text">Dashboard</span></a></li>
        <li><a href="<?= site_url('pets') ?>"><span class="icon">&#128021;</span> <span class="sidebar-text">Pets</span></a></li>
        <!-- Item Dropdown -->
        <li>
            <a href="#itemSubmenu" class="item-toggle" onclick="toggleItemMenu(event)" style="display: flex; align-items: center; justify-content: space-between;">
                <span>
                    <span class="icon">&#128230;</span> <span class="sidebar-text">Item</span>
                </span>
                <span class="dropdown-icon" style="margin-left: auto; font-size: 1em;">&#9662;</span>
            </a>
            <ul id="itemSubmenu" class="submenu" style="padding: 0px; max-height: 0; overflow: hidden; transition: max-height 0.3s ease;">
                <li style="padding: 0.3rem 0.3rem 0.3rem 1.5rem" class="mx-3"><a href="<?= site_url('item/add') ?>">Add</a></li>
                <li style="padding: 0.3rem 0.3rem 0.3rem 1.5rem" class="mx-3"><a href="<?= site_url('item/delete') ?>">Delete</a></li>
                <li style="padding: 0.3rem 0.3rem 0.3rem 1.5rem" class="mx-3"><a href="<?= site_url('item/list') ?>">List</a></li>
                <li style="padding: 0.3rem 0.3rem 0.3rem 1.5rem" class="mx-3"><a href="<?= site_url('item/update') ?>">Update</a></li>
            </ul>
        </li>
        <li><a href="<?= site_url('settings') ?>"><span class="icon">&#9881;</span> <span class="sidebar-text">Settings</span></a></li>

    </ul>
</aside>

<script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle');
    const sidebarText = document.querySelectorAll('.sidebar-text');
    const logoText = document.getElementById('sidebar-logo-text');

    function setSidebarState(minimized) {
        if (minimized) {
            sidebar.classList.add('minimized');
            sidebarText.forEach(el => el.style.display = 'none');
            logoText.style.display = 'none';
        } else {
            sidebar.classList.remove('minimized');
            sidebarText.forEach(el => el.style.display = '');
            logoText.style.display = '';
        }
    }

    // Load state from localStorage
    let minimized = localStorage.getItem('sidebar-minimized') === 'true';
    setSidebarState(minimized);

    toggleBtn.addEventListener('click', function() {
        minimized = !sidebar.classList.contains('minimized');
        setSidebarState(minimized);
        localStorage.setItem('sidebar-minimized', minimized);
    });

    // Responsive: minimize by default on small screens
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            setSidebarState(true);
        } else if (!localStorage.getItem('sidebar-minimized')) {
            setSidebarState(false);
        }
    });

    function toggleItemMenu(event) {
        event.preventDefault();
        const submenu = document.getElementById('itemSubmenu');
        if (submenu.style.maxHeight && submenu.style.maxHeight !== '0px') {
            submenu.style.maxHeight = '0';
        } else {
            submenu.style.maxHeight = submenu.scrollHeight + 'px';
        }
    }
</script>