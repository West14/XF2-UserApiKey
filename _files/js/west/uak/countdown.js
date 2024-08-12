window.West = window.West || {}

!function ($, window, document) {
    'use strict'

    West.Countdown = XF.Element.newHandler({
        options: {
            timestamp: null,
        },

        target: null,
        countdownDate: null,

        init () {
            if (!this.options.timestamp || this.options <= (new Date().getTime() / 1000))
            {
                return
            }

            this.target = this.$target[0]

            this.countdownDate = new Date(this.options.timestamp * 1000)
            this.targetDays = this.target.querySelector('.countdown-days')
            this.targetHours = this.target.querySelector('.countdown-hours')
            this.targetMinutes = this.target.querySelector('.countdown-minutes')
            this.targetSeconds = this.target.querySelector('.countdown-seconds')

            this.tick()
            setInterval(this.tick.bind(this), 1000)
        },

        tick () {
            const diff = (this.countdownDate - new Date()) / 1000
            if (diff <= 0) {
                window.location.reload()
                return
            }

            const days = Math.floor(diff / (60 * 60 * 24))
            const hours = Math.floor(diff % (60 * 60 * 24) / (60 * 60))
            const minutes = Math.floor((diff % (60 * 60)) / (60))
            const seconds = Math.floor(diff % 60)

            const remaining = {
                days: this.pluralize(days, 'day'),
                hours: this.pluralize(hours, 'hour'),
                minutes: this.pluralize(minutes, 'minute'),
                seconds: this.pluralize(seconds, 'second'),
            }

            this.targetDays.innerText = remaining.days
            this.targetHours.innerText = remaining.hours
            this.targetMinutes.innerText = remaining.minutes
            this.targetSeconds.innerText = remaining.seconds
        },

        pluralize (number, unit) {
            const mod100 = number % 100

            if (mod100 >= 11 && mod100 <= 14) {
                return XF.phrase(this.getPhraseName(unit, 's_many'), { '{x}': number })
            }

            const count = number % 10
            let phrase
            if (count === 1) {
                phrase = this.getPhraseName(unit, '')
            }
            else if (count > 1 && count < 5) {
                phrase = this.getPhraseName(unit, 's_few')
            }
            else {
                phrase = this.getPhraseName(unit, 's_many')
            }

            return XF.phrase(phrase, { '{x}': number })
        },

        getPhraseName(unit, suffix) {
            return `wuak_x_${unit}${suffix}`
        }
    })

    XF.Element.register('wuak-countdown', 'West.Countdown')

} (jQuery, window, document)