process.env.NODE_ENV = 'production';
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
    ...defaultConfig,
    plugins: [...defaultConfig.plugins],
    entry: {
        blocks: path.resolve(__dirname, 'src/blocks.js'),
    },
    resolve: {
        alias: {
            ...defaultConfig.resolve.alias,
            '@Controls': path.resolve(__dirname, 'wpsp-config/controls'),
            '@Components': path.resolve(__dirname, 'src/components/'),
        },
    },
    module: {
        rules: [
            defaultConfig.module.rules[0],
            {
                test: /\.(scss|css)$/,
                exclude: [/node_modules/, /style/],
                use: [
                    {
                        loader: 'style-loader',
                        options: {
                            injectType: 'lazySingletonStyleTag',
                            attributes: { id: 'wpsp-editor-styles' }
                        },
                    },
                    'css-loader',
                    'sass-loader',
                ],
            },
            {
                ...defaultConfig.module.rules[
                defaultConfig.module.rules.length - 1
                ],
                exclude: [/node_modules/, /editor/],
            },
        ],
    },
    output: {
        ...defaultConfig.output,
        // eslint-disable-next-line no-undef
        path: path.resolve(__dirname, 'dist')
    }
};
