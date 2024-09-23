export default {
    content: ({ parent, props }) => ({
        class: [
            'flex',
            {
                'flex-col': props.fullScreen
            },
            {
                'flex-col': parent.props.thumbnailsPosition === 'top' || parent.props.thumbnailsPosition === 'bottom',
                'flex-row': parent.props.thumbnailsPosition === 'right' || parent.props.thumbnailsPosition === 'left'
            }
        ]
    }),
    itemsContainer: ({ parent, props }) => ({
        class: [
            'group',
            'flex relative',
            {
                'grow shrink w-0 justify-center': props.fullScreen
            },
            {
                'flex-col': parent.props.indicatorsPosition === 'bottom' || parent.props.indicatorsPosition === 'top',
                'flex-row items-center': parent.props.indicatorsPosition === 'left' || parent.props.indicatorsPosition === 'right'
            },
            {
                'order-2': parent.props.thumbnailsPosition === 'top' || parent.props.thumbnailsPosition === 'left',
                'flex-row': parent.props.thumbnailsPosition === 'right'
            }
        ]
    }),
    items: ({ parent }) => ({
        class: [
            'flex h-full relative',
            {
                'order-1': parent.props.indicatorsPosition === 'bottom' || parent.props.indicatorsPosition === 'right',
                'order-2': parent.props.indicatorsPosition === 'top' || parent.props.indicatorsPosition === 'left'
            }
        ]
    }),
    item: {
        class: [
            // Flex
            'flex justify-center items-center h-full w-full',

            // Sizing
            'h-full w-full'
        ]
    },
    thumbnails: ({ parent }) => ({
        class: [
            // Flex
            'flex flex-col shrink-0',

            {
                'order-1': parent.props.thumbnailsPosition === 'top' || parent.props.thumbnailsPosition === 'left'
            },

            // Misc
            'overflow-auto'
        ]
    }),
    thumbnailContent: ({ parent }) => ({
        class: [
            // Flex
            'flex',

            // Spacing
            'py-4 px-1',

            // Colors
            'bg-black/90',

            {
                'flex-row': parent.props.thumbnailsPosition === 'top' || parent.props.thumbnailsPosition === 'bottom',
                'flex-col grow': parent.props.thumbnailsPosition === 'right' || parent.props.thumbnailsPosition === 'left'
            }
        ]
    }),
    thumbnailPrevButton: {
        class: [
            // Positioning
            'self-center relative',

            // Display & Flexbox
            'flex shrink-0 justify-center items-center overflow-hidden',

            // Spacing
            'm-2',

            // Appearance
            'bg-transparent text-white w-8 h-8 rounded-full transition duration-200 ease-in-out',

            // Hover Effects
            'hover:bg-surface-0/10 hover:text-white',

            // Focus Effects
            'focus:outline-none focus:outline-offset-0 focus:ring-1 focus:ring-primary-500 dark:focus:ring-primary-400'
        ]
    },
    thumbnailsViewport: {
        class: 'overflow-hidden w-full'
    },
    thumbnailItems: ({ parent }) => ({
        class: [
            'flex',
            {
                'flex-col h-full': parent.props.thumbnailsPosition === 'right' || parent.props.thumbnailsPosition === 'left'
            }
        ]
    }),
    thumbnailItem: ({ parent }) => ({
        class: [
            // Flexbox
            'flex items-center justify-center',
            'grow shrink-0',

            // Sizing
            {
                'w-full md:w-[25%] lg:w-[20%]': parent.props.thumbnailsPosition === 'top' || parent.props.thumbnailsPosition === 'bottom'
            },

            // Misc
            'overflow-auto',
            'cursor-pointer',
            'opacity-50',

            // States
            '[&[data-p-active="true"]]:opacity-100',
            'hover:opacity-100',

            // Transitions
            'transition-opacity duration-300'
        ]
    }),
    thumbnailNextButton: {
        class: [
            // Positioning
            'self-center relative',

            // Display & Flexbox
            'flex shrink-0 justify-center items-center overflow-hidden',

            // Spacing
            'm-2',

            // Appearance
            'bg-transparent text-white w-8 h-8 rounded-full transition duration-200 ease-in-out',

            // Hover Effects
            'hover:bg-surface-0/10 hover:text-white',

            // Focus Effects
            'focus:outline-none focus:outline-offset-0 focus:ring-1 focus:ring-primary-500 dark:focus:ring-primary-400'
        ]
    },
    indicatorList: ({ parent }) => ({
        class: [
            // flex
            'flex items-center justify-center',

            // Spacing
            'p-4',

            // Indicators Position
            {
                'order-2': parent.props.indicatorsPosition == 'bottom',
                'order-1': parent.props.indicatorsPosition == 'top',
                'order-1 flex-col': parent.props.indicatorsPosition == 'left',
                'flex-col order-2': parent.props.indicatorsPosition == 'right'
            },
            {
                'absolute z-10 bg-black/50': parent.props.showIndicatorsOnItem
            },

            {
                'bottom-0 left-0 w-full items-start': parent.props.indicatorsPosition == 'bottom' && parent.props.showIndicatorsOnItem,
                'top-0 left-0 w-full items-start': parent.props.indicatorsPosition == 'top' && parent.props.showIndicatorsOnItem,
                'left-0 top-0 h-full items-start': parent.props.indicatorsPosition == 'left' && parent.props.showIndicatorsOnItem,
                'right-0 top-0 h-full items-start': parent.props.indicatorsPosition == 'right' && parent.props.showIndicatorsOnItem
            }
        ]
    }),
    indicator: ({ parent }) => ({
        class: [
            {
                'mr-2': parent.props.indicatorsPosition == 'bottom' || parent.props.indicatorsPosition == 'top',
                'mb-2': parent.props.indicatorsPosition == 'left' || parent.props.indicatorsPosition == 'right'
            }
        ]
    }),
    indicatorButton: ({ context }) => ({
        class: [
            // Size
            'w-4 h-4',

            // Appearance
            'rounded-full transition duration-200',

            // Focus Effects
            'focus:outline-none focus:outline-offset-0 focus:ring-1 focus:ring-primary-500 dark:focus:ring-primary-400',

            // Conditional Appearance: Not Highlighted
            { 'bg-surface-200 hover:bg-surface-300 dark:bg-surface-700 dark:hover:bg-surface-600': !context.highlighted },

            // Conditional Appearance: Highlighted
            { 'bg-primary hover:bg-primary-emphasis': context.highlighted }
        ]
    }),
    mask: {
        class: ['fixed top-0 left-0 w-full h-full', 'flex items-center justify-center', 'bg-black/90']
    },
    closeButton: {
        class: [
            // Positioning
            '!absolute top-0 right-0',

            // Display & Flexbox
            'flex justify-center items-center overflow-hidden',

            // Spacing
            'm-2',

            // Appearance
            'text-white bg-transparent w-12 h-12 rounded-full transition duration-200 ease-in-out',

            // Hover Effect
            'hover:text-white hover:bg-surface-0/10',

            // Focus Effects
            'focus:outline-none focus:outline-offset-0 focus:ring-1 focus:ring-primary-500 dark:focus:ring-primary-400'
        ]
    },
    closeIcon: {
        class: 'w-6 h-6'
    },
    prevButton: ({ parent }) => ({
        class: [
            // Display & Flexbox
            'inline-flex justify-center items-center overflow-hidden',

            // Appearance
            'bg-transparent text-white w-16 h-16 transition duration-200 ease-in-out rounded-md',
            {
                'opacity-0 group-hover:opacity-100': parent.props.showItemNavigatorsOnHover
            },

            // Spacing
            'mx-2',

            // Positioning
            'top-1/2 mt-[-0.5rem] left-0',
            {
                '!absolute': !parent.state.containerVisible && parent.props.showItemNavigators,
                '!fixed': parent.state.containerVisible
            },

            // Hover Effect
            'hover:bg-surface-0/10 hover:text-white',

            // Focus Effects
            'focus:outline-none focus:outline-offset-0 focus:ring-1 focus:ring-primary-500 dark:focus:ring-primary-400'
        ]
    }),
    nextButton: ({ parent }) => ({
        class: [
            // Display & Flexbox
            'inline-flex justify-center items-center overflow-hidden',

            // Appearance
            'bg-transparent text-white w-16 h-16 transition duration-200 ease-in-out rounded-md',
            {
                'opacity-0 group-hover:opacity-100': parent.props.showItemNavigatorsOnHover
            },

            // Spacing
            'mx-2',

            // Positioning
            'top-1/2 mt-[-0.5rem] right-0',
            {
                '!absolute': !parent.state.containerVisible && parent.props.showItemNavigators,
                '!fixed': parent.state.containerVisible
            },

            // Hover Effect
            'hover:bg-surface-0/10 hover:text-white',

            // Focus Effects
            'focus:outline-none focus:outline-offset-0 focus:ring-1 focus:ring-primary-500 dark:focus:ring-primary-400'
        ]
    }),
    caption: {
        class: [
            // Positioning
            'absolute bottom-0 left-0 w-full',

            // Appearance
            'bg-black/50 text-white p-4'
        ]
    },
    transition: {
        enterFromClass: 'opacity-0 scale-75',
        enterActiveClass: 'transition-all duration-150 ease-in-out',
        leaveActiveClass: 'transition-all duration-150 ease-in',
        leaveToClass: 'opacity-0 scale-75'
    }
};
