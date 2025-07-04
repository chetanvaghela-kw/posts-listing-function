# Custom Posts Listing

This feature allows you to display custom post listings in your WordPress theme using a flexible function with multiple loading options like pagination, load more, and infinite scroll.

---

## 🔧 Installation Instructions

1. **Upload PHP File**

   - Upload the `post-listing-functions.php` file to your theme directory.

   - In your theme's `functions.php` file, include it like this:

     ```php
     require_once get_template_directory() . '/post-listing-functions.php';
     ```

   - **If you place the file in an `includes` folder:**

     ```php
     require_once get_template_directory() . '/includes/post-listing-functions.php';
     ```

2. **Add JavaScript File**

   - Place the `posts.js` file inside your theme's `assets/js/` folder.

---

## 🚀 Usage

To display a list of posts, use the following function:

```php
<?php custom_post_listing( 'POST_TYPE', 'pagination', 3 ); ?>
```

## 📌 Parameters

- **Post Type** (`string`)  
  The post type to query. Leave empty to use the default `post`.

- **Loading Method** (`string`)  
  Determines how posts are loaded. Available options:  
  - `pagination` – Traditional pagination links  
  - `loadmore` – Load more button  
  - `scroll` – Infinite scroll on page scroll

- **Posts Per Page** (`int`)  
  Number of posts to show per page/load.
