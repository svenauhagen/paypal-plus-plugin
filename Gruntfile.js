module.exports = function( grunt ) {
	var fs = require( 'fs' );

	/**
	 * Goes through the given directory to return all files and folders recursively
	 * @author Ash Blue ash@blueashes.com
	 * @example getFilesRecursive('./folder/sub-folder');
	 * @requires Must include the file system module native to NodeJS, ex. var fs = require('fs');
	 * @param {string} folder Folder location to search through
	 * @returns {object} Nested tree of the found files
	 */
	function getJSClasses( folder ) {
		var fileContents = fs.readdirSync( folder ),
			classNames = [],
			stats;

		fileContents.forEach( function( fileName ) {
			stats = fs.lstatSync( folder + '/' + fileName );

			if ( stats.isDirectory() ) {
				classNames = classNames.concat( getJSClasses( folder + '/' + fileName ) )
			} else {
				classNames.push( fileName.replace( /\.[^/.]+$/, "" ) );
			}
		} );

		return classNames;
	}

	var baseConfig = {
		name: 'PayPal Plus for WooCommerce',

		pkg: grunt.file.readJSON( 'package.json' ),

		scripts: {
			src : 'resources/js/',
			dest: 'assets/js/'
		},
		styles : {
			src : 'resources/scss/',
			dest: 'assets/css/'
		}
	};
	grunt.initConfig( {
		config    : baseConfig,
		/**
		 * @see {@link https://github.com/jmreidy/grunt-browserify grunt-browserify}
		 * @see {@link https://github.com/substack/node-browserify browserify}
		 */
		browserify: {
			babelify: {
				options: {
					transform: [
						/**
						 * @see {@link https://github.com/babel/babelify babelify}
						 */
						[ 'babelify' ]
					]
				},
				expand : true,
				cwd    : '<%= config.scripts.src %>',
				src    : [ '*.js' ],
				dest   : '<%= config.scripts.dest %>'
			}
		},
		/**
		 * @see {@link https://github.com/gruntjs/grunt-contrib-cssmin grunt-contrib-cssmin}
		 * @see {@link https://github.com/jakubpawlowicz/clean-css clean-css}
		 */
		cssmin    : {
			styles: {
				options: {
					compatibility: 'ie8'
				},
				expand : true,
				cwd    : '<%= config.styles.dest %>',
				src    : [ '*.css', '!*.min.css' ],
				dest   : '<%= config.styles.dest %>',
				ext    : '.min.css'
			}
		},
		compress  : {
			main: {
				options: {
					archive: function() {
						return 'dist/PayPal Plus for WooCommerce - ' + new Date().getTime() + '.zip';
					},
					level  : 6
				},
				files  : [
					{
						expand: true,
						src   : [
							'./assets/**/*', // CSS and JS
							'./src/**/*', // PHP code
							'./vendor/composer/**/*', // Autoloader
							'./vendor/autoload.php', // Autoloader
							'./lib/PayPal/**/*', // Paypal PHP SDK
							'./vendor/psr/**/*', // Used by PayPal
							'./LICENSE', // Maybe not needed
							'./paypalplus-woocommerce.php', // Main plugin file
							'./readme.txt',

							'!./*/**/README.md',
						]
					}
				]
			}
		},
		delegate  : {
			babelify: {
				src : [
					'.babelrc',
					'<%= config.scripts.src %>**/*.js'
				],
				task: 'browserify:babelify'
			},

			sass_convert: {
				src : [ '<%= config.styles.src %>**/*.scss' ],
				task: 'sass:convert'
			}
		},
		/**
		 * @see {@link https://github.com/gruntjs/grunt-contrib-uglify grunt-contrib-uglify}
		 * @see {@link https://github.com/mishoo/UglifyJS UglifyJS}
		 */
		uglify    : {
			scripts: {
				options: {
					ASCIIOnly       : true,
					preserveComments: false,
					mangle          : {
						except: getJSClasses( baseConfig.scripts.src )
					}
				},
				expand : true,
				cwd    : '<%= config.scripts.dest %>',
				src    : [ '*.js', '!*.min.js' ],
				dest   : '<%= config.scripts.dest %>',
				ext    : '.min.js'
			}
		},
		watch     : {
			options: {
				spawn: false
			},
			scripts: {
				files: [
					'.eslintrc',
					'<%= config.scripts.src %>**/*.js'
				],
				tasks: [
					'newer:delegate:babelify',
					'changed:uglify'
				]
			},

			styles: {
				files: [ '<%= config.styles.src %>**/*.scss' ],
				tasks: [
					'newer:delegate:sass_convert',
					'changed:postcss',
					'changed:cssmin'
				]
			}
		},
		/**
		 * @see {@link https://github.com/nDmitry/grunt-postcss grunt-postcss}
		 * @see {@link https://github.com/postcss/postcss PostCSS}
		 */
		postcss   : {
			styles: {
				options: {
					processors : [
						/**
						 * @see {@link https://github.com/postcss/autoprefixer Autoprefixer}
						 */
						require( 'autoprefixer' )( {
							browsers: '> 1%, last 2 versions, IE 8',
							cascade : false
						} )
					],
					failOnError: true
				},
				expand : true,
				cwd    : '<%= config.styles.dest %>',
				src    : [ '*.css', '!*.min.css' ],
				dest   : '<%= config.styles.dest %>'
			}
		},
		sass      : {
			options: {
				unixNewlines: true,
				noCache     : true
			},

			check: {
				options: {
					check: true
				},
				src    : '<%= config.styles.src %>*.scss'
			},

			convert: {
				options: {
					sourcemap: 'none',
					style    : 'expanded'
				},
				expand : true,
				cwd    : '<%= config.styles.src %>',
				src    : [ '*.scss' ],
				dest   : '<%= config.styles.dest %>',
				ext    : '.css'
			}
		}
	} );

	require( 'load-grunt-tasks' )( grunt );

	grunt.registerTask( 'default', [ 'watch' ] );
	grunt.registerTask( 'scripts', [ 'browserify:babelify', 'uglify' ] );
	grunt.registerTask( 'styles', [ 'sass:convert', 'postcss', 'cssmin' ] );
	grunt.registerTask( 'release', [ 'styles', 'scripts', 'compress' ] );
};
