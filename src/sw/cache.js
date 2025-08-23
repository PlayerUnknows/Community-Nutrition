export const CACHE_NAME = "static-cache-v1";

export const RESOURCES_TO_CACHE = [
  "./",
  "./index.php",
  "...../src/view/admin.php",
  ".../view/signup.php",
  "./src/script/login.js",
  "./src/script/logout.js",
  "./src/script/nav.js",
  "./src/script/signup.js",
  "./assets/css/bootstrap.min.css",
  "./assets/css/index.css",
  "./assets/css/admin.css",
  "./assets/dist/sweetalert.js",
  "./assets/dist/jquery.js",
  "./assets/dist/popper.js",
  "./assets/dist/bootstrap.min.js",
  "./assets/dist/sweetalert.js",
];

export async function cacheResources() {
  const cache = await caches.open(CACHE_NAME);
  try {
    await cache.addAll(RESOURCES_TO_CACHE);
    console.log("All resources cached successfully");
  } catch (error) {
    console.error("Failed to cache resources:", error);
  }
}

export async function cleanupOldCaches() {
  const cacheNames = await caches.keys();
  return Promise.all(
    cacheNames.map((cacheName) => {
      if (cacheName !== CACHE_NAME) {
        console.log("Deleting old cache:", cacheName);
        return caches.delete(cacheName);
      }
    })
  );
}
