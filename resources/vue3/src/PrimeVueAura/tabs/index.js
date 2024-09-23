export default {
    root: ({ props }) => ({
        class: ['flex flex-col', { '[&>[data-pc-name=tablist]]:overflow-hidden': props.scrollable }]
    })
};
