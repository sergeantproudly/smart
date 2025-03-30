import gulp from 'gulp';
import { deleteAsync } from 'del';
import browserSync from 'browser-sync';
import concat from 'gulp-concat';
import autoprefixer from 'gulp-autoprefixer';
import cleanCSS from 'gulp-clean-css';
import gulpSass from 'gulp-sass';
import sourcemaps from 'gulp-sourcemaps';
import babel from 'gulp-babel';
import uglify from 'gulp-uglify';
import imagemin from 'gulp-imagemin';
import sass from 'sass';

const sassCompiled = gulpSass(sass);

const jsFiles = [
	'./src/js/jquery-3.1.1.min.js',
	'./src/js/jquery-ui.min.js',
	'./src/js/jquery.touchSwipe.min.js',
	'./src/js/jquery.mousewheel.min.js',
	'./src/js/jquery.animateNumber.js',
	'./src/js/jquery.magnific-popup.js',
	'./src/js/slick.min.js',
	'./src/js/barba.min.js',
	'./src/js/preloader.js',
	'./src/js/common.js',
	'./src/js/checks.js',
	'./src/js/messages.js',
	'./src/js/scripts.js'
];

function styles() {
	return gulp.src('./src/css/style.scss')
		.pipe(concat('style.css'))
		.pipe(autoprefixer({
			overrideBrowsersList: ['> 0.1%'],
			cascade: false
		}))
		.pipe(sassCompiled.sync({ outputStyle: 'compressed' }).on('error', sassCompiled.logError))
		.pipe(gulp.dest('./assets/css'))
		.pipe(browserSync.stream());
}

function scripts() {
	return gulp.src(jsFiles)
		.pipe(concat('scripts.js'))
		.pipe(uglify({
			toplevel: true
		}))
		.pipe(gulp.dest('./assets/js'))
		.pipe(browserSync.stream());
}

function images() {
	return gulp.src('./src/images/*')
		.pipe(imagemin())
		.pipe(gulp.dest('./assets/images'));
}

function fonts() {
	return gulp.src('./src/fonts/**/*')
		.pipe(gulp.dest('./assets/fonts'));
}

function copy() {
	return gulp.src('./src/images/*')
		.pipe(gulp.dest('./assets/images'));
}

function watch() {
	browserSync.init({
		proxy: 'smart.local'
	});

	gulp.watch('./src/css/**/*.scss', styles);
	gulp.watch('./src/js/**/*.js', scripts);
	gulp.watch('./src/images/**/*', copy);
	gulp.watch('./**/*.htm').on('change', browserSync.reload);
	gulp.watch('./**/*.php').on('change', browserSync.reload);
}

function clean() {
	return deleteAsync(['assets/*']);
}

gulp.task('images', images);
gulp.task('watch', watch);

gulp.task('build', gulp.series(clean,
	gulp.parallel(styles, scripts, images, fonts)
));

gulp.task('dev', gulp.series('build', 'watch'));
