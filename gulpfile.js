const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const cleanCSS = require('gulp-clean-css');
const rename = require('gulp-rename');
const sourcemaps = require('gulp-sourcemaps');

// Define SCSS file paths
const paths = {
    scss: {
        main: './assets/scss/style.scss',
        admin: './assets/scss/admin.scss',
        all: './assets/scss/**/*.scss' // Watch all SCSS files
    },
    css: './assets/css/'
};

// Function to compile Main SCSS
function compileMainSass() {
    return gulp.src(paths.scss.main)
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(postcss([autoprefixer()]))
        .pipe(cleanCSS())
        .pipe(rename({ suffix: '.min' }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(paths.css));
}

// Function to compile Admin SCSS
function compileAdminSass() {
    return gulp.src(paths.scss.admin)
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(postcss([autoprefixer()]))
        .pipe(cleanCSS())
        .pipe(rename({ suffix: '.min' }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(paths.css));
}

// Watch files for changes
function watchFiles() {
    gulp.watch(paths.scss.all, gulp.series(compileMainSass, compileAdminSass));
}

// Default Gulp Task (Compile both and watch)
exports.default = gulp.series(gulp.parallel(compileMainSass, compileAdminSass), watchFiles);
exports.build = gulp.parallel(compileMainSass, compileAdminSass);
