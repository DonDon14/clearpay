# CSS Organization - ClearPay Dashboard

## Structure

### Main CSS Files
- `global.css` - Global layout styles and utilities
- `header.css` - Header component styles  
- `footer.css` - Footer component styles
- `dashboard.css` - Dashboard-specific styles

### Components Directory
- `components/sidebar-complete.css` - Complete sidebar component with collapse functionality

### Legacy Files (Archived)
- `components/sidebar.css` - Original sidebar styles (deprecated)
- `components/sidebar-collapse.css` - Original collapse styles (deprecated)  
- `components/sidebar-force-collapse.css` - Force collapse overrides (deprecated)

## Sidebar Component Features

### Desktop Features
- Smooth collapse/expand animation
- Persistent state using localStorage
- Hover effects and active state styling
- Arrow expand button when collapsed
- Clean gradient background design

### Mobile Features  
- Slide-in/slide-out mobile menu
- Touch-friendly interface
- Responsive breakpoints at 576px

### Usage
The sidebar automatically handles:
- Desktop vs mobile behavior
- State persistence
- Smooth animations
- Accessibility features

## File Loading Order
1. Bootstrap CSS (external)
2. Global layout CSS
3. Component CSS files
4. Page-specific CSS

This ensures proper CSS cascading and prevents style conflicts.