export default {
    table: {
        class: [
            // Spacing & Position
            'mx-auto my-0',

            // Table Style
            'border-spacing-0 border-separate'
        ]
    },
    cell: {
        class: [
            // Alignment
            'text-center align-top',

            // Spacing
            'py-0 px-3'
        ]
    },
    node: ({ context }) => ({
        class: [
            'relative inline-block',

            // Spacing
            'py-3 px-4',

            // Shape
            'border',
            'rounded-md',
            'border-surface-200 dark:border-surface-700',
            // Color
            {
                'text-surface-600 dark:text-white/80': !context?.selected,
                'bg-surface-0 dark:bg-surface-900': !context?.selected,
                'bg-highlight': context?.selected
            },

            // States
            {
                'hover:bg-surface-100 dark:hover:bg-surface-800': context?.selectable && !context?.selected,
                'hover:bg-highlight-emphasis': context?.selectable && context?.selected
            },

            { 'cursor-pointer': context?.selectable }
        ]
    }),
    lineCell: {
        class: [
            // Alignment
            'text-center align-top',

            // Spacing
            'py-0 px-3'
        ]
    },
    connectorDown: {
        class: [
            // Spacing
            'mx-auto my-0',

            // Size
            'w-px h-[20px]',

            // Color
            'bg-surface-200 dark:bg-surface-700'
        ]
    },
    connectorLeft: ({ context }) => ({
        class: [
            // Alignment
            'text-center align-top',

            // Spacing
            'py-0 px-3',

            // Shape
            'rounded-none border-r',
            { 'border-t': context.lineTop },

            // Color
            'border-surface-200 dark:border-surface-700'
        ]
    }),
    connectorRight: ({ context }) => ({
        class: [
            // Alignment
            'text-center align-top',

            // Spacing
            'py-0 px-3',

            // Shape
            'rounded-none',

            // Color
            { 'border-t border-surface-200 dark:border-surface-700': context.lineTop }
        ]
    }),
    nodeCell: {
        class: 'text-center align-top py-0 px-3'
    },
    nodeToggleButton: {
        class: [
            // Position
            'absolute bottom-[-0.75rem] left-2/4 -ml-3',
            'z-20',

            // Flexbox
            'flex items-center justify-center',

            // Size
            'w-6 h-6',

            // Shape
            'rounded-full',
            'border border-surface-200 dark:border-surface-700',

            // Color
            'bg-inherit text-inherit',

            // Focus
            'focus:outline-none focus:outline-offset-0 focus:ring-1 focus:ring-primary-500 dark:focus:ring-primary-400',

            // Misc
            'cursor-pointer no-underline select-none'
        ]
    },
    nodeToggleButtonIcon: {
        class: [
            // Position
            'static inline-block',

            // Size
            'w-4 h-4'
        ]
    }
};
