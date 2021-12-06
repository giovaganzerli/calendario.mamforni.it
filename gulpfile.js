"use strict";

var gulp        = require('gulp');
var sass        = require('gulp-sass');
var minify      = require('gulp-minify');
var watch       = require('gulp-watch');

var scssOptions = {
    errLogToConsole: true,
    outputStyle: 'compressed'
};

gulp.task('scssMain', function() {

    return gulp.src('src/assets/scss/*.scss')
        .pipe(sass(scssOptions))
        .pipe(gulp.dest('src/assets/css/'));
});

gulp.task('scssComponents', function() {
    
    return gulp.src('src/assets/scss/components/*.scss')
        .pipe(sass(scssOptions))
        .pipe(gulp.dest('src/assets/css/components/'));
});

gulp.task('scssViews', function() {
    
    return gulp.src('src/assets/scss/views/*.scss')
        .pipe(sass(scssOptions))
        .pipe(gulp.dest('src/assets/css/views/'));
});

// Watch task
gulp.task('default', gulp.series(['scssMain', 'scssComponents', 'scssViews']), function() {
    
    gulp.series(['scssMain']);
    gulp.series(['scssComponents']);
    gulp.series(['scssViews']);
    
    gulp.watch('src/assets/scss/**/*.scss', gulp.series(['scssMain', 'scssComponents', 'scssViews']));
    
});