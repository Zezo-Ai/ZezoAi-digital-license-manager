/**
 * Fix WordPress admin menu highlighting for hash-based routes.
 * WordPress doesn't natively understand hash routes, so we need to
 * manually manage the "current" class on menu items.
 *
 * @param {string} slug - The menu page slug (e.g., 'dlm-licenses')
 */
export default function menuFix(slug) {
    const menuRoot = document.querySelector(`#toplevel_page_${slug}`)

    if (!menuRoot) {
        return
    }

    const submenuItems = menuRoot.querySelectorAll('.wp-submenu li')

    // Handle click events on submenu items
    menuRoot.addEventListener('click', function(e) {
        const clickedItem = e.target.closest('li')
        if (!clickedItem || !clickedItem.closest('.wp-submenu')) {
            return
        }

        submenuItems.forEach(item => item.classList.remove('current'))
        clickedItem.classList.add('current')
    })

    // Highlight correct menu item on initial page load
    const currentUrl = window.location.href
    const currentPath = currentUrl.substring(currentUrl.indexOf('admin.php'))

    submenuItems.forEach(item => {
        const link = item.querySelector('a')
        if (link && link.getAttribute('href') === currentPath) {
            item.classList.add('current')
        }
    })

    // Update menu on hash change
    window.addEventListener('hashchange', function() {
        const hash = window.location.hash || '#/'
        const pageParam = new URLSearchParams(window.location.search).get('page')

        submenuItems.forEach(item => {
            const link = item.querySelector('a')
            if (!link) return

            const href = link.getAttribute('href')
            const linkHash = href.includes('#') ? href.substring(href.indexOf('#')) : '#/'

            // Remove current from all
            item.classList.remove('current')

            // Add current to matching item
            if (linkHash === hash || (hash === '#/' && linkHash === '#/') ||
                (hash.startsWith('#/licenses') && linkHash === '#/') ||
                (hash.startsWith('#/generators') && linkHash === '#/generators') ||
                (hash.startsWith('#/activations') && linkHash === '#/activations') ||
                (hash.startsWith('#/settings') && linkHash === '#/settings')) {
                item.classList.add('current')
            }
        })
    })
}
