const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require('path');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const HtmlWebpackPlugin = require('html-webpack-plugin');
const globule = require('globule');
const FixStyleOnlyEntries = require("webpack-fix-style-only-entries");

const MODE = "development";
// production

// ソースマップの利用有無(productionのときはソースマップを利用しない)
const enabledSourceMap = MODE === "development";

const buildDefault = {
    // モード値を production に設定すると最適化された状態で、
    // development に設定するとソースマップ有効でJSファイルが出力される
    mode: MODE,

    // メインとなるJavaScriptファイル（エントリーポイント）
    entry: {
        "script" : "./src/js/script.js",
        "editor" : "./src/js/editor.js",
        "style.css"  : './src/scss/style.scss',
        "editor.css" : "./src/scss/editor.scss"
    },


    // ファイルの出力設定
    output: {
        path: path.resolve(__dirname, './'),
        filename: './[name].js',
    },

    module: {
        rules: [
            // Sassファイルの読み込みとコンパイル
            {
                test: /\.scss/, // 対象となるファイルの拡張子
                use: [
                    // CSSファイルを書き出すオプションを有効にする
                    {
                        loader: MiniCssExtractPlugin.loader,
                    },
                    // CSSをバンドルするための機能
                    {
                        loader: "css-loader",
                        options: {
                            // オプションでCSS内のurl()メソッドの取り込みを禁止する
                            url: false,
                            // ソースマップの利用有無
                            sourceMap: enabledSourceMap,
                            importLoaders: 2,
                        },
                    },
                    {
                        loader: "postcss-loader",
                        options: {
                            // PostCSS側でもソースマップを有効にする
                            // sourceMap: true,
                            postcssOptions: {
                            plugins: [
                                // Autoprefixerを有効化
                                // ベンダープレフィックスを自動付与する
                                ["autoprefixer", { grid: true }],
                            ],
                            },
                        },
                    },
                    {
                        loader: "sass-loader",
                        options: {
                            // ソースマップの利用有無
                            sourceMap: enabledSourceMap,
                        },
                    },
                ],
            },
            {
                // node_module内のcss
                test: /node_modules\/(.+)\.css$/,
                use: [
                {
                    loader: 'style-loader',
                },
                {
                    loader: 'css-loader',
                    options: { url: false },
                },
                ],
                sideEffects: true,
            },
            {
                test: [/\.js$/, /\.jsx$/],
                use: [
                    {
                        loader: 'babel-loader',
                        options: {
                            presets: ["@babel/preset-env","@babel/preset-react"]
                        }
                    }
                ]
            }
        ],
    },

    resolve: {
        extensions: [ '.ts', '.js', '.json', '.jsx' ]
    },

    plugins: [
        new FixStyleOnlyEntries(),
        new MiniCssExtractPlugin({
            filename: "./[name]",
        }),
    ],
    
    target: ["web", "es5"],

}


module.exports = buildDefault;