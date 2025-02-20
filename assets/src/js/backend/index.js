const requireAll = require.context('./', false, /\.js$/);

requireAll.keys().forEach((fileName) => {
  if (fileName === './index.js') return; // Avoid importing itself
  requireAll(fileName);
});
