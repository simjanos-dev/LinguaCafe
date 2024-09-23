export default {
    root: ({ props }) => ({
        class: [
            // Flexbox and Position
            'inline-flex',
            'relative',

            // Shape
            'rounded-md',
            { 'shadow-lg': props.raised },

            '[&>[data-pc-name=pcbutton]]:rounded-tr-none',
            '[&>[data-pc-name=pcbutton]]:rounded-br-none',
            '[&>[data-pc-name=pcdropdown]]:rounded-tl-none',
            '[&>[data-pc-name=pcdropdown]]:rounded-bl-none',
            '[&>[data-pc-name=pcmenu]]:min-w-full'
        ]
    })
};
