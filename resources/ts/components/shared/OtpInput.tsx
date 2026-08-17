import { Fragment, useRef } from 'react'
import type { ChangeEvent, ClipboardEvent, KeyboardEvent } from 'react'

interface OtpInputProps {
    value: string
    onChange: (value: string) => void
    length?: number
    autoFocus?: boolean
}

export default function OtpInput({ value, onChange, length = 6, autoFocus }: OtpInputProps) {
    const inputsRef = useRef<(HTMLInputElement | null)[]>([])
    const digits = Array.from({ length }, (_, i) => value[i] ?? '')
    const mid = Math.floor(length / 2)

    const setDigit = (index: number, digit: string) => {
        const chars = value.padEnd(length, ' ').split('')
        chars[index] = digit
        onChange(chars.join('').replace(/\s+$/, ''))
    }

    const onDigitChange = (index: number, e: ChangeEvent<HTMLInputElement>) => {
        const raw = e.target.value.replace(/\D/g, '')

        if (!raw) {
            setDigit(index, '')

            return
        }

        setDigit(index, raw[raw.length - 1])

        if (index < length - 1) {
            inputsRef.current[index + 1]?.focus()
        }
    }

    const onKeyDown = (index: number, e: KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Backspace' && !digits[index] && index > 0) {
            inputsRef.current[index - 1]?.focus()
        }
    }

    const onPaste = (e: ClipboardEvent<HTMLInputElement>) => {
        const pasted = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, length)

        if (pasted) {
            e.preventDefault()
            onChange(pasted)
            inputsRef.current[Math.min(pasted.length, length - 1)]?.focus()
        }
    }

    return (
        <div className="d-flex align-items-center justify-content-center gap-2">
            {digits.map((digit, index) => (
                <Fragment key={index}>
                    {index === mid && <span className="otp-divider">&ndash;</span>}
                    <input
                        ref={(el) => {
                            inputsRef.current[index] = el
                        }}
                        type="text"
                        inputMode="numeric"
                        maxLength={1}
                        className="form-control otp-box"
                        value={digit}
                        autoFocus={autoFocus && index === 0}
                        onChange={(e) => onDigitChange(index, e)}
                        onKeyDown={(e) => onKeyDown(index, e)}
                        onPaste={onPaste}
                    />
                </Fragment>
            ))}
        </div>
    )
}
