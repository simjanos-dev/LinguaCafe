export default {
    root: {
        class: [
            'relative',

            '[&>[data-pc-name=inputicon]]:absolute',
            '[&>[data-pc-name=inputicon]]:top-1/2',
            '[&>[data-pc-name=inputicon]]:-mt-2',
            '[&>[data-pc-name=inputicon]]:text-surface-900/60 dark:[&>[data-pc-name=inputicon]]:text-white/60',

            '[&>[data-pc-name=inputicon]:first-child]:left-3',
            '[&>[data-pc-name=inputicon]:last-child]:right-3',

            '[&>[data-pc-name=inputtext]:first-child]:pr-10',
            '[&>[data-pc-name=inputtext]:last-child]:pl-10',

            // filter
            '[&>[data-pc-extend=inputicon]]:absolute',
            '[&>[data-pc-extend=inputicon]]:top-1/2',
            '[&>[data-pc-extend=inputicon]]:-mt-2',
            '[&>[data-pc-extend=inputicon]]:text-surface-900/60 dark:[&>[data-pc-extend=inputicon]]:text-white/60',

            '[&>[data-pc-extend=inputicon]:first-child]:left-3',
            '[&>[data-pc-extend=inputicon]:last-child]:right-3'
        ]
    }
};
