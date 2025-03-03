const path = require('path')
const { CleanWebpackPlugin } = require('clean-webpack-plugin')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')

module.exports = {
    entry: {
        export: './src/export.ts',
        graph: './src/graph.ts',
        index: './src/index.ts',
        map: './src/map.ts',
    },
    output: {
        filename: '[name].[contenthash].js',
        path: path.resolve(__dirname, '../public/dist'),
    },
    mode: 'production',
    resolve: {
        extensions: ['.ts', '.js'],
    },
    module: {
        rules: [
            {
                test: /\.css$/,
                use: [MiniCssExtractPlugin.loader, 'css-loader'],
            },
            {
                test: /\.scss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    'postcss-loader',
                    'sass-loader',
                ],
            },
            {
                test: /\.(woff|woff2|eot|ttf|otf)$/,
                type: 'asset/resource',
            },
            {
                test: /\.ts$/,
                exclude: /node_modules/,
                use: 'ts-loader',
            },
        ],
    },
    optimization: {
        splitChunks: {
            chunks: 'all',
            name: (module, chunks, cacheGroupKey) => {
                const allChunksNames = chunks.map((item) => item.name).join('-')
                return `${cacheGroupKey}-${allChunksNames}`
            },
        },
    },
    plugins: [
        new CleanWebpackPlugin(),
        new MiniCssExtractPlugin({
            filename: 'index.[contenthash].css',
        }),
    ],
}
