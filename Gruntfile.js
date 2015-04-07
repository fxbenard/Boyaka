module.exports = function(grunt) {

// Load multiple grunt tasks using globbing patterns
require('load-grunt-tasks')(grunt);

// Project configuration.
grunt.initConfig({
	pkg: grunt.file.readJSON('package.json'),

makepot: {
	target: {
		options: {
			domainPath: '/languages', // Where to save the POT file.
			exclude: ['build/.*'],
			mainFile: 'bloom-french.php', // Main project file.
			potFilename: 'bloom-french.pot', // Name of the POT file.
			potHeaders: {
				poedit: true, // Includes common Poedit headers.
				'x-poedit-keywordslist': true // Include a list of all possible gettext functions.
			},
			type: 'wp-plugin', // Type of project (wp-plugin or wp-theme).
			updateTimestamp: true, // Whether the POT-Creation-Date should be updated without other changes.
			updatePoFiles: true, // Whether to update PO files in the same directory as the POT file.
			processPot: function(pot, options) {
				pot.headers['report-msgid-bugs-to'] = 'http://fxbenard.com/';
				pot.headers['last-translator'] = 'fxbenard <http://fxbenard.com/>';
				pot.headers['language-team'] = 'fxbenard <fx@fxbenard.com>';
				pot.headers['language'] = 'en_US';
					var translation, // Exclude meta data from pot.
					excluded_meta = [
						'Plugin Name of the plugin/theme',
						'Plugin URI of the plugin/theme',
						'Author of the plugin/theme',
						'Author URI of the plugin/theme'
					];
				for (translation in pot.translations['']) {
					if ('undefined' !== typeof pot.translations[''][translation].comments.extracted) {
						if (excluded_meta.indexOf(pot.translations[''][translation].comments.extracted) >= 0) {
							console.log('Excluded meta: ' + pot.translations[''][translation].comments.extracted);
							delete pot.translations[''][translation];
						}
					}
				}
				return pot;
			}
		}
	}
},

		// Clean up build directory
		clean: {
			main: ['build/<%= pkg.name %>']
		},

		// Copy the theme into the build directory
		copy: {
			main: {
				src:  [
					'**',
					'!node_modules/**',
					'!build/**',
					'!.git/**',
					'!Gruntfile.js',
					'!package.json',
					'!.gitignore',
					'!.gitmodules',
					'!.tx/**',
					'!**/Gruntfile.js',
					'!**/package.json',
					'!**/README.md',
					'!**/*~'
				],
				dest: 'build/<%= pkg.name %>/'
			}
		},

		//Compress build directory into <name>.zip and <name>-<version>.zip
		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: './build/<%= pkg.name %>-<%= pkg.version %>.zip'
				},
				expand: true,
				cwd: 'build/<%= pkg.name %>/',
				src: ['**/*'],
				dest: '<%= pkg.name %>/'
			}
		},

});

// Default task. - grunt makepot
grunt.registerTask( 'default', 'makepot' );

// Build task(s).
grunt.registerTask( 'build', [ 'clean', 'copy', 'compress' ] );

};