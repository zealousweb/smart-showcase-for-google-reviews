const requireAll = require.context('./', false, /\.js$/);
requireAll.keys().forEach(requireAll);