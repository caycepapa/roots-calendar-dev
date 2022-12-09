"use strict";

//import { registerBlockType } from '@wordpress/blocks';
//import { RichText } from '@wordpress/block-editor';

export default function(){

    ( function( blocks, element) {

        console.log(wp);
        const { registerBlockType } = wp.blocks;
        const { RichText,MediaUpload } = wp.blockEditor;
        const { Button } = wp.components;

        registerBlockType(
            'roots/faq',
            {
                title: 'Q&Aブロック',
                icon: 'admin-comments',
                category: 'layout',
                description: 'Q&Aのブロックです',
                
                styles: [
                    {
                        name: 'default',
                        label: '左吹き出し', 
                        isDefault: true 
                    },
                    {
                        name: 'right',
                        label: '右吹き出し'
                    },
                ],
                attributes: {
                    myQuestion: {
                        type: 'string',
                    },
                    myAnswer: {
                        type: 'string'
                    },
                },

                edit: ({className, attributes: { myQuestion, myAnswer }, setAttributes }) => {

                    const onSelectImage = ( media ) => {
                        setAttributes( {
                            mediaURL: media.url,
                            mediaID: media.id,
                        } );
                    };

                    return(
                        <div className='roots-faq-blocks'>
                            <div className="roots-faq-blocks__q">
                                <RichText
                                    value = { myQuestion }
                                    onChange ={ (newContent) => setAttributes({ myQuestion: newContent }) }
                                    placeholder={ '質問を入力してください' }
                                />
                            </div>
                            <div className="roots-faq-blocks__a">
                                <RichText
                                    value = { myAnswer }
                                    onChange ={ (newContent) => setAttributes({ myAnswer: newContent }) }
                                    placeholder={ '回答を入力してください' }
                                />
                            </div>
                        </div>
                    );
                },
                save: ({className , attributes: { myQuestion, myAnswer}}) => {
                    return(
                        <div className='roots-faq-blocks'>
                            <div className="roots-faq-blocks__q">
                                <RichText.Content
                                    value = { myQuestion }
                                />
                            </div>
                            <div className="roots-faq-blocks__a">
                                <RichText.Content
                                    value = { myAnswer }
                                />
                            </div>
                        </div>
                    );
                },
            }
        )

    } )(
        window.wp.blocks,
        window.wp.element,
    );

}