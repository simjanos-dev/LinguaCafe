export default {
    root: {
        class: [
            // Space
            'p-4',

            // Shape
            'rounded-md',
            'border-none',

            // Color
            'bg-surface-0 dark:bg-surface-900',
            'text-surface-700 dark:text-white/80',
            '[&_[data-pc-name=pcfilter]]:w-full'
        ]
    },
    wrapper: {
        class: ['overflow-auto']
    },
    container: {
        class: [
            // Spacing
            'm-0 p-0',

            // Misc
            'list-none overflow-auto'
        ]
    },
    node: {
        class: ['p-0 my-[2px] mx-0 first:mt-0', 'rounded-md', 'focus:outline-none focus:outline-offset-0 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:focus:ring-primary-400 focus:z-10']
    },
    nodeContent: ({ context, props }) => ({
        class: [
            // Flex and Alignment
            'flex items-center',

            // Shape
            'rounded-md',

            // Spacing
            'py-1 px-2 gap-2',

            // Colors
            context.selected ? 'bg-highlight text-primary-600 dark:text-white' : 'bg-transparent text-surface-600 dark:text-white/70',

            // States
            { 'hover:bg-surface-50 dark:hover:bg-[rgba(255,255,255,0.03)]': (props.selectionMode == 'single' || props.selectionMode == 'multiple') && !context.selected },

            // Transition
            'transition-shadow duration-200',

            { 'cursor-pointer select-none': props.selectionMode == 'single' || props.selectionMode == 'multiple' }
        ]
    }),
    nodeToggleButton: ({ context }) => ({
        class: [
            // Flex and Alignment
            'inline-flex items-center justify-center',

            // Shape
            'border-0 rounded-full',

            // Size
            'w-7 h-7',

            // Colors
            'bg-transparent',
            {
                'text-surface-600 dark:text-white/70': !context.selected,
                'text-primary-600 dark:text-white': context.selected,
                invisible: context.leaf
            },

            // States
            'hover:bg-surface-200/20 dark:hover:bg-surface-500/20',
            'focus:outline-none focus:outline-offset-0 focus:ring-1 focus:ring-primary-500 dark:focus:ring-primary-400',

            // Transition
            'transition duration-200',

            // Misc
            'cursor-pointer select-none'
        ]
    }),
    nodeIcon: ({ context }) => ({
        class: [
            // Space
            'mr-2',

            // Color
            {
                'text-surface-600 dark:text-white/70': !context.selected,
                'text-primary-600 dark:text-white': context.selected
            }
        ]
    }),
    nodeLabel: ({ context }) => ({
        class: [
            {
                'text-surface-600 dark:text-white/70': !context.selected,
                'text-primary-600 dark:text-white': context.selected
            }
        ]
    }),
    nodeChildren: {
        class: ['m-0 list-none p-0 pl-4 [&:not(ul)]:pl-0 [&:not(ul)]:my-[2px]']
    },
    loadingIcon: {
        class: ['text-surface-500 dark:text-surface-0/70', 'absolute top-[50%] right-[50%] -mt-2 -mr-2 animate-spin']
    }
    // pcFilterContainer: {
    //     root: {
    //         class: '[&>[data-pc-name=inputtext]]:w-full'
    //     }
    // }
};
