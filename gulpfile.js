const gulp = require('gulp')
const gulpPhpcs = require('gulp-phpcs')
const gulpZip = require('gulp-zip')
const gulpDel = require('del')
const minimist = require('minimist')
const fs = require('fs')
const pump = require('pump')
const usage = require('gulp-help-doc')
const { exec } = require('child_process')
const semver = require('semver')
const PACKAGE_NAME = 'paypalplus-woocommerce'
const PACKAGE_DESTINATION = './dist'
const PACKAGE_PATH = `${PACKAGE_DESTINATION}/${PACKAGE_NAME}`
const ENV_DEVELOPMENT = 'development'
const ENV_PRODUCTION = 'production'
const BASE_PATH = './'

const options = minimist(process.argv.slice(2), {
  string: [
    'packageVersion',
    'compressPath',
    'compressedName'
  ],
  default: {
    compressPath: process.compressPath || '.',
    packageVersion: process.packageVersion || '',
    compressedName: process.compressedName || ''
  }
})

function setupEncore ({ environment, basePath }) {
  return function encore (done) {
    environment = (environment === ENV_DEVELOPMENT) ? 'dev' : environment

    exec(
      `./node_modules/.bin/encore ${environment} --env.basePath ${basePath}`,
      (error, stdout, stderr) => {
        if (error) {
          throw new Error(error)
        }

        done()
      }
    )
  }
}

/**
 * Check the Package Version value is passed to the script
 * @param done
 * @throws Error if the package version option isn't found
 */
async function validatePackageVersion (done) {
  await 1

  if (!('packageVersion' in options) || options.packageVersion === '') {
    done()
  }

  if (semver.valid(options.packageVersion) === null) {
    throw new Error(
      'Invalid package version, please follow MAJOR.MINOR.PATCH semver convention.'
    )
  }

  done()
}

/**
 * Run composer for dist
 * @param done
 * @returns {Promise<any>}
 */
function composer (done) {
  return exec(
    `composer install --prefer-dist --optimize-autoloader --no-dev --no-scripts --working-dir=${PACKAGE_PATH}`
  )
}

/**
 * PHPCS Task
 * @returns {*}
 */
function phpcs () {
  return gulp
    .src('./src/**/*.php')
    .pipe(gulpPhpcs({
      bin: './vendor/bin/phpcs',
      standard: 'Inpsyde'
    }))
    .pipe(
      gulpPhpcs.reporter('fail', { failOnFirst: true })
    )
}

/**
 * Create the package
 * @returns {*}
 */
function copyPackageFiles (done) {
  return new Promise(() => {
    pump(
      gulp.src([
        './languages/**/*',
        './lib/**/*',
        '!./lib/__classes',
        './public/**/*',
        './src/**/*',
        './LICENSE',
        './paypalplus-woocommerce.php',
        './uninstall.php',
        './composer.json'
      ], {
        base: './'
      }),
      gulp.dest(PACKAGE_PATH),
      done
    )
  })
}

/**
 * Compress the package
 * @returns {*}
 */
function compressPackage (done) {
  const { packageVersion, compressPath } = options
  const timeStamp = new Date().getTime()

  if (!fs.existsSync(PACKAGE_DESTINATION)) {
    throw new Error(
      `Cannot create package, ${PACKAGE_DESTINATION} doesn't exists.`)
  }

  gulpDel.sync(
    [
      `${PACKAGE_DESTINATION}/**/changelog.txt`,
      `${PACKAGE_DESTINATION}/**/changelog.md`,
      `${PACKAGE_DESTINATION}/**/CHANGELOG.md`,
      `${PACKAGE_DESTINATION}/**/CHANGELOG`,
      `${PACKAGE_DESTINATION}/**/README`,
      `${PACKAGE_DESTINATION}/**/README.md`,
      `${PACKAGE_DESTINATION}/**/readme.md`,
      `${PACKAGE_DESTINATION}/**/composer.json`,
      `${PACKAGE_DESTINATION}/**/composer.lock`,
      `${PACKAGE_DESTINATION}/**/phpcs.xml`,
      `${PACKAGE_DESTINATION}/**/phpcs.xml.dist`,
      `${PACKAGE_DESTINATION}/**/phpunit.xml`,
      `${PACKAGE_DESTINATION}/**/phpunit.xml.dist`,
      `${PACKAGE_DESTINATION}/**/.gitignore`,
      `${PACKAGE_DESTINATION}/**/.travis.yml`,
      `${PACKAGE_DESTINATION}/**/.scrutinizer.yml`,
      `${PACKAGE_DESTINATION}/**/.gitattributes`,
      `${PACKAGE_DESTINATION}/**/bitbucket-pipelines.yml`,
      `${PACKAGE_DESTINATION}/**/test`,
      `${PACKAGE_DESTINATION}/**/tests`,
      `${PACKAGE_DESTINATION}/**/bin`,
      `${PACKAGE_DESTINATION}/vendor/**/readme.txt`,
      `${PACKAGE_DESTINATION}/vendor/**/CONTRIBUTING.md`,
      `${PACKAGE_DESTINATION}/vendor/**/CONTRIBUTING`,
      `${PACKAGE_DESTINATION}/**/public/**/entrypoints.json`,
      `${PACKAGE_DESTINATION}/**/public/**/manifest.json`
    ]
  )

  return new Promise(() => {
    exec(
      `git log -n 1 | head -n 1 | sed -e 's/^commit //' | head -c 8`,
      {},
      (error, stdout) => {
        let shortHash = error ? timeStamp : stdout
        const compressedName = options.compressedName ||
          `${PACKAGE_NAME}-${packageVersion}-${shortHash}`

        pump(
          gulp.src(`${PACKAGE_DESTINATION}/**/*`, {
            base: PACKAGE_DESTINATION
          }),
          gulpZip(`${compressedName}.zip`),
          gulp.dest(
            compressPath,
            {
              base: PACKAGE_DESTINATION,
              cwd: './'
            }
          ),
          done
        )
      }
    )
  })
}

/**
 * Delete content within the Dist directory
 * @returns {*}
 */
async function cleanDist () {
  return await gulpDel(PACKAGE_DESTINATION)
}

/**
 * Gulp Help
 * @returns {Promise}
 */
function help () {
  return usage(gulp)
}

/**
 * Run Tests
 *
 * @task {tests}
 */
exports.tests = gulp.series(
  phpcs
)

const buildAssetsTask = gulp.series(
  setupEncore({
    ...options,
    basePath: BASE_PATH,
    environment: ENV_DEVELOPMENT
  })
)

/**
 * Create the plugin package distribution.
 *
 * @task {dist}
 * @arg {packageVersion} Package version, the version must to be conformed to semver.
 * @arg {compressPath} Where the resulting package zip have to be stored.
 * @arg {compressedName} The name to give to the package instead of the default one.
 */
exports.dist = gulp.series(
  validatePackageVersion,
  cleanDist,
  setupEncore({
    ...options,
    basePath: PACKAGE_PATH,
    environment: ENV_PRODUCTION
  }),
  copyPackageFiles,
  composer,
  compressPackage,
  cleanDist
)

/**
 * Build the assets for development
 *
 * @task {buildAssets}
 */
exports.buildAssets = buildAssetsTask
exports.help = help
exports.default = help
