const path = require('path');
const webpack = require('webpack');

/**
 * Resolve path based on dirname
 * @param part
 * @returns {*}
 */
function resolve(part)
{
    return path.resolve(__dirname, part);
}

module.exports = [
    {
        mode: 'production',
        devtool: 'eval-source-map',
        entry: {
            admin: './resources/js/admin.js',
            front: './resources/js/front.js',
            paypalRedirect: './resources/js/payPalRedirect.js'
        },
        output: {
            path: resolve('public/js'),
            filename: '[name].min.js'
        },
        module: {
            rules: [
                {
                    test: /\.js$/,
                    include: [
                        resolve('/public/js/admin.min.js'),
                        resolve('/public/js/front.min.js'),
                        resolve('/public/js/payPalRedirect.min.js'),
                    ],
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: ['@babel/preset-env']
                        }
                    }
                },
            ]
        },
        plugins: [
            new webpack.SourceMapDevToolPlugin({
                filename: '[file].map'
            }),
        ],
    },
];
