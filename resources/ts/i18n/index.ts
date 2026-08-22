import i18n from 'i18next'
import { initReactI18next } from 'react-i18next'

import enCommon from './locales/en/common.json'
import enAuth from './locales/en/auth.json'
import enHome from './locales/en/home.json'
import enAccount from './locales/en/account.json'
import enAdmin from './locales/en/admin.json'
import enCmcPost from './locales/en/cmcPost.json'
import enDonations from './locales/en/donations.json'
import enOauth from './locales/en/oauth.json'
import enErrors from './locales/en/errors.json'

import arCommon from './locales/ar/common.json'
import arAuth from './locales/ar/auth.json'
import arHome from './locales/ar/home.json'
import arAccount from './locales/ar/account.json'
import arAdmin from './locales/ar/admin.json'
import arCmcPost from './locales/ar/cmcPost.json'
import arDonations from './locales/ar/donations.json'
import arOauth from './locales/ar/oauth.json'
import arErrors from './locales/ar/errors.json'

export const defaultNS = 'common'

i18n.use(initReactI18next).init({
    lng: 'en',
    fallbackLng: 'en',
    defaultNS,
    ns: ['common', 'auth', 'home', 'account', 'admin', 'cmcPost', 'donations', 'oauth', 'errors'],
    resources: {
        en: {
            common: enCommon,
            auth: enAuth,
            home: enHome,
            account: enAccount,
            admin: enAdmin,
            cmcPost: enCmcPost,
            donations: enDonations,
            oauth: enOauth,
            errors: enErrors,
        },
        ar: {
            common: arCommon,
            auth: arAuth,
            home: arHome,
            account: arAccount,
            admin: arAdmin,
            cmcPost: arCmcPost,
            donations: arDonations,
            oauth: arOauth,
            errors: arErrors,
        },
    },
    interpolation: {
        escapeValue: false,
    },
})

export default i18n
