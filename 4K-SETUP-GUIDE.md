# 4K Digital Sign Setup Guide

## Overview

Your digital sign system has been upgraded with a modern, 4K-optimized display designed specifically for your 43" 4K UHD TV (3840x2160, 16:9 aspect ratio).

## New Features

### 🎨 Modern Design
- **Animated gradient background** that shifts colors smoothly
- **Glass-morphism effects** with backdrop blur and transparency
- **Smooth transitions** with cubic-bezier animations
- **Gradient text effects** with glowing animations
- **Shimmer effects** on content areas

### 📺 Optimized Layout
- **Main Events Area** (left 2/3): Large, prominent event display with image support
- **Presidents Section** (top right): Historical president photos with smooth transitions
- **Board Members** (bottom right): Current leadership display
- **News Ticker** (bottom): Scrolling announcements across the full width

### 🎉 Enhanced Events
- **Image Support**: Upload photos/graphics for each event
- **Larger Text**: Optimized for 4K viewing distance
- **Better Typography**: Modern fonts with text shadows and gradients
- **Flexible Layout**: Content adapts based on whether images are present

### 📢 News Ticker
- **Simplified Announcements**: No more start/end dates - just active/inactive
- **Continuous Scroll**: Smooth scrolling ticker at bottom of screen
- **Live Preview**: See how your ticker will look in the admin panel

## Setup Steps

### 1. Update Database for Event Images

Run this script to add image support to events:
```
http://yoursite.com/elks/update-events-images.php
```

### 2. Access the New Admin Panels

- **Events with Images**: `http://yoursite.com/elks/admin/events-with-images.php`
- **Ticker Announcements**: `http://yoursite.com/elks/admin/announcements-ticker.php`

### 3. View the 4K Display

- **4K Display**: `http://yoursite.com/elks/display/index-4k.php`

## Content Guidelines

### Events
- **Title**: Keep concise but descriptive (displays large)
- **Images**: Recommended 800x600px or larger, landscape orientation
- **Description**: Can be longer, will wrap nicely
- **Date/Time**: Automatically formatted for readability

### Announcements (Ticker)
- **Title**: Short and punchy (main ticker text)
- **Content**: Optional additional details (appears after dash)
- **Sort Order**: Lower numbers appear first in ticker rotation

### Presidents & Board
- **Images**: Portrait orientation works best (280x350px display size)
- **High Resolution**: Use high-quality images for 4K clarity

## Display Settings for Your TV

### Recommended TV Settings
- **Picture Mode**: Movie or Cinema (for accurate colors)
- **Backlight**: 80-90% (for bright room viewing)
- **Contrast**: 85-90%
- **Brightness**: 45-50%
- **Color**: 50-55%
- **Sharpness**: 0-10% (avoid over-sharpening)

### Browser Settings (if using browser)
- **Full Screen**: Press F11 for full-screen mode
- **Zoom**: Set to 100% for proper scaling
- **Hardware Acceleration**: Enable for smooth animations

## Performance Optimizations

### 4K-Specific Improvements
- **Direct Database Queries**: No API calls for faster loading
- **Optimized Images**: Proper sizing for 4K display
- **CSS Animations**: Hardware-accelerated transitions
- **Reduced Refresh**: 10-minute page refresh (vs 5-minute)

### Timing Adjustments
- **Events**: 15 seconds per slide (more time to read)
- **Presidents/Board**: 8 seconds per slide
- **Ticker**: 30-second scroll cycle

## File Structure

```
/display/
├── index.html          # Original display
├── index-optimized.php # MySQL-optimized display
└── index-4k.php       # New 4K display ⭐

/admin/
├── events-with-images.php      # New events admin ⭐
├── announcements-ticker.php    # New announcements admin ⭐
└── [existing admin files]

/uploads/
├── presidents/
├── board/
└── events/            # New events images folder ⭐
```

## Troubleshooting

### Display Issues
- **Blurry Text**: Check TV sharpness settings, ensure 4K resolution
- **Slow Animations**: Enable hardware acceleration in browser
- **Layout Problems**: Verify browser zoom is at 100%

### Content Issues
- **Images Not Showing**: Check file permissions on uploads/events/ folder
- **Ticker Not Updating**: Verify announcements are marked as "Active"
- **Events Missing**: Check that event dates are in the future

### Performance Issues
- **Slow Loading**: Use the 4K display (index-4k.php) instead of API version
- **Memory Issues**: Restart browser periodically on Pi devices

## Browser Recommendations

### For Raspberry Pi
- **Chromium**: Best performance with hardware acceleration
- **Firefox**: Good alternative if Chromium has issues

### Settings for Kiosk Mode
```bash
# Chromium kiosk mode
chromium-browser --kiosk --disable-infobars --disable-session-crashed-bubble http://yoursite.com/elks/display/index-4k.php

# Auto-start on boot (add to autostart)
@chromium-browser --kiosk --disable-infobars http://yoursite.com/elks/display/index-4k.php
```

## Maintenance

### Regular Tasks
- **Content Updates**: Use the admin panels to keep content fresh
- **Image Optimization**: Compress large images before uploading
- **Browser Restart**: Restart browser weekly to clear memory
- **Database Cleanup**: Remove old events periodically

### Monitoring
- **Display Check**: Visit display URL daily to ensure it's working
- **Content Review**: Check that all content is current and relevant
- **Performance**: Monitor for any slowdowns or issues

## Support

If you encounter issues:

1. **Run Diagnostics**: `http://yoursite.com/elks/debug-display.php`
2. **Check Browser Console**: F12 → Console tab for JavaScript errors
3. **Verify Database**: Ensure MySQL connection is working
4. **Test Components**: Try individual admin pages to isolate issues

The new 4K display should provide a much more engaging and professional appearance for your digital signage system!
