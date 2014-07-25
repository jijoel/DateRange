var gulp = require('gulp');
var phpunit = require('gulp-phpunit');
var plumber = require('gulp-plumber');

gulp.task('phpunit', function() {
    gulp.src('./tests/**/*Test.php')
        .pipe(plumber())
        .pipe(phpunit('phpunit --group=now', {debug:false}))
        .pipe(plumber.stop());
});

gulp.task('watch', function () {
    gulp.watch('**/*.php', ['phpunit']);
});

// What tasks does running gulp trigger?
gulp.task('default', ['phpunit']);
