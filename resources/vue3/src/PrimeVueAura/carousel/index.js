export default {
    root: {
        class: [
            // Flexbox
            'flex flex-col'
        ]
    },
    contentContainer: {
        class: [
            // Flexbox & Overflow
            'flex flex-col overflow-auto'
        ]
    },
    content: ({ props }) => ({
        class: [
            // Flexbox
            'flex',

            // Orientation
            {
                'flex-row': props.orientation !== 'vertical',
                'flex-col': props.orientation == 'vertical'
            },
            '[&>[data-pc-extend=button]]:self-center'
        ]
    }),
    viewport: {
        class: [
            // Overflow & Width
            'overflow-hidden w-full'
        ]
    },
    itemList: ({ props }) => ({
        class: [
            // Flexbox
            'flex',

            // Orientation & Sizing
            {
                'flex-row': props.orientation !== 'vertical',
                'flex-col h-full': props.orientation == 'vertical'
            }
        ]
    }),
    item: ({ props }) => ({
        class: [
            // Flexbox
            'flex shrink-0 grow ',

            // Size
            {
                'w-full sm:w-[50%] md:w-[33.333333333333336%]': props.orientation !== 'vertical',

                'w-full h-full': props.orientation == 'vertical'
            }
        ]
    }),
    itemClone: ({ props }) => ({
        class: [
            // Flexbox
            'flex shrink-0 grow',
            'unvisible',

            // Size
            {
                'w-full sm:w-[50%] md:w-[33.333333333333336%]': props.orientation !== 'vertical',

                'w-full h-full': props.orientation == 'vertical'
            }
        ]
    }),
    indicatorList: {
        class: [
            // Flexbox & Alignment
            'flex flex-row justify-center flex-wrap'
        ]
    },
    indicator: {
        class: [
            // Spacing
            'mr-2 mb-2'
        ]
    },
    indicatorButton: ({ context }) => ({
        class: [
            // Sizing & Shape
            'w-8 h-2 rounded-md',

            // Transitions
            'transition duration-200',

            // Focus Styles
            'focus:outline-none focus:outline-offset-0 focus:ring-1 focus:ring-primary-500 dark:focus:ring-primary-400',

            // Color & Background
            {
                'bg-surface-200 hover:bg-surface-300 dark:bg-surface-700 dark:hover:bg-surface-600': !context.highlighted,
                'bg-primary hover:bg-primary-emphasis': context.highlighted
            }
        ]
    })
};
