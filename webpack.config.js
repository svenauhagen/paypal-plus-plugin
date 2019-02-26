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
        devtool: 'source-map',
        entry: {
            admin: './resources/js/admin.js'
        },
        output: {
            path: resolve('public/js'),
            filename: '[name].js'
        },
        module: {
            rules: [
                {
                    test: /\.js$/,
                    include: [
                        resolve('/public/js/admin.js'),
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
