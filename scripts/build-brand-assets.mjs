#!/usr/bin/env node

import {
    mkdirSync,
    readFileSync,
    writeFileSync,
} from 'node:fs'
import { dirname, join, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'

const pluginRoot = resolve(dirname(fileURLToPath(import.meta.url)), '..')
const brandDir = join(pluginRoot, 'assets', 'brand')
const sourceDir = join(brandDir, 'source')
const runtimeDir = join(pluginRoot, 'assets', 'img')
const wordpressDir = join(pluginRoot, '.wordpress-org')

const colors = {
    teal: '#147D82',
    deep: '#0F6266',
    aqua: '#73C0BD',
    mist: '#EAF6F5',
    ink: '#102A2C',
    white: '#FFFFFF',
}

const markPaths = `
  <path fill-rule="evenodd" d="M8 15h11c10 0 18 7.5 18 17s-8 17-18 17H8V15zm7 7v20h4c6 0 11-4.5 11-10s-5-10-11-10h-4z"/>
  <path d="M34 28h26v8h-6v10h-7V36h-6v7h-7z"/>`

function write(relativePath, contents) {
    const destination = join(pluginRoot, relativePath)
    mkdirSync(dirname(destination), { recursive: true })
    writeFileSync(destination, `${contents.trim()}\n`)
}

function markSvg(color) {
    return `
<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64" fill="${color}">
${markPaths}
</svg>`
}

function iconSvg() {
    return `
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
  <rect width="256" height="256" rx="56" fill="${colors.teal}"/>
  <g transform="translate(32 32) scale(3)" fill="${colors.white}">
${markPaths}
  </g>
</svg>`
}

function coloredMarkPaths(color) {
    return markPaths.replaceAll('<path', `<path fill="${color}"`)
}

function outlineText(filename, prefix) {
    const source = readFileSync(join(sourceDir, filename), 'utf8')
    const viewBox = source.match(/viewBox="0 0 ([\d.]+) ([\d.]+)"/)
    const body = source.match(/<\/defs>\s*([\s\S]*?)\s*<\/svg>/)

    if (!viewBox || !body) {
        throw new Error(`Unable to parse outlined text for ${prefix}`)
    }

    const glyphs = new Map(
        [...source.matchAll(/<g id="glyph-0-(\d+)">\s*([\s\S]*?)\s*<\/g>/g)]
            .map(match => [match[1], match[2]]),
    )
    const uses = [...body[1].matchAll(
        /<use xlink:href="#glyph-0-(\d+)" x="([\d.-]+)" y="([\d.-]+)"\/>/g,
    )]

    if (!uses.length) {
        throw new Error(`Unable to expand outlined glyphs for ${prefix}`)
    }

    const paths = uses
        .map(([, glyph, x, y]) => glyphs.get(glyph)
            ? `<g transform="translate(${x} ${y})">${glyphs.get(glyph)}</g>`
            : '')
        .filter(Boolean)
        .join('\n')

    return {
        width: Number(viewBox[1]),
        height: Number(viewBox[2]),
        defs: '',
        body: `<g fill="currentColor">\n${paths}\n</g>`,
    }
}

function embeddedMark(color, x = 0, y = 0, width = 56, height = 52) {
    const scale = Math.min(width, height) / 64
    const offsetX = x + (width - (64 * scale)) / 2
    const offsetY = y + (height - (64 * scale)) / 2

    return `
<g transform="translate(${offsetX} ${offsetY}) scale(${scale})" aria-hidden="true">
${coloredMarkPaths(color)}
</g>`
}

function coloredText(outline, color) {
    return outline.body.replaceAll('currentColor', color)
}

function lockupSvg(wordmark, pro, reversed = false) {
    const foreground = reversed ? colors.white : colors.ink
    const markColor = reversed ? colors.white : colors.teal
    const badgeWidth = pro ? 44 : 0
    const badgeHeight = 24
    const width = 60 + wordmark.width + (pro ? badgeWidth + 4 : 0)
    const badgeX = 60 + wordmark.width + 4
    const badgeY = (52 - badgeHeight) / 2

    return `
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="${width}" height="52" viewBox="0 0 ${width} 52">
  <defs>
${wordmark.defs}
${pro ? pro.defs : ''}
  </defs>
  ${embeddedMark(markColor)}
  <g transform="translate(58 0)">
${coloredText(wordmark, foreground)}
  </g>
${pro ? `  <rect x="${badgeX}" y="${badgeY}" width="${badgeWidth}" height="${badgeHeight}" rx="${badgeHeight / 2}" fill="${reversed ? '#FFFFFF1F' : colors.mist}" stroke="${reversed ? '#FFFFFF66' : colors.aqua}"/>
  <g transform="translate(${badgeX + 1} 10)">
${coloredText(pro, reversed ? colors.white : colors.deep)}
  </g>` : ''}
</svg>`
}

function bannerSvg(wordmark, tagline) {
    return `
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="772" height="250" viewBox="0 0 772 250">
  <defs>
${wordmark.defs}
${tagline.defs}
  </defs>
  <rect width="772" height="250" fill="${colors.deep}"/>
  <g fill="none" stroke="${colors.aqua}" stroke-opacity=".11">
    ${Array.from({ length: 12 }, (_, index) => `<path d="M${500 + (index * 24)} 0v250"/>`).join('\n    ')}
    ${Array.from({ length: 11 }, (_, index) => `<path d="M500 ${index * 24}h272"/>`).join('\n    ')}
  </g>
    <path d="M520 44h190M538 206h184M698 44v162" fill="none" stroke="${colors.aqua}" stroke-opacity=".28"/>
    <circle cx="520" cy="44" r="5" fill="${colors.aqua}"/>
    <circle cx="538" cy="206" r="5" fill="${colors.aqua}"/>
    <circle cx="698" cy="44" r="5" fill="${colors.aqua}"/>
    <circle cx="722" cy="206" r="5" fill="${colors.aqua}"/>
    <g transform="translate(483 -2) scale(4.2)" opacity=".16">
${coloredMarkPaths(colors.aqua)}
    </g>
    <g transform="translate(42 30)">
      ${embeddedMark(colors.white, 0, 0, 56, 52)}
      <g transform="translate(58 0)">
${coloredText(wordmark, colors.white)}
      </g>
    </g>
    <g transform="translate(42 101)">
${coloredText(tagline, colors.mist)}
    </g>
    <rect x="42" y="173" width="74" height="4" rx="2" fill="${colors.aqua}"/>
    <circle cx="128" cy="175" r="4" fill="${colors.teal}"/>
    <circle cx="142" cy="175" r="4" fill="${colors.aqua}" fill-opacity=".65"/>
</svg>`
}

mkdirSync(brandDir, { recursive: true })
mkdirSync(runtimeDir, { recursive: true })
mkdirSync(wordpressDir, { recursive: true })

const wordmark = outlineText('wordmark.svg', 'dlm-wordmark')
const pro = outlineText('pro.svg', 'dlm-pro')
const tagline = outlineText('tagline.svg', 'dlm-tagline')

write('assets/brand/dlm-mark.svg', markSvg(colors.teal))
write('assets/brand/dlm-mark-ink.svg', markSvg(colors.ink))
write('assets/brand/dlm-mark-reversed.svg', markSvg(colors.white))
write('assets/brand/dlm-icon.svg', iconSvg())
write('assets/brand/dlm-lockup.svg', lockupSvg(wordmark, null))
write('assets/brand/dlm-lockup-reversed.svg', lockupSvg(wordmark, null, true))
write('assets/brand/dlm-lockup-pro.svg', lockupSvg(wordmark, pro))
write('assets/brand/dlm-wordpress-banner.svg', bannerSvg(wordmark, tagline))
write('assets/img/logo.svg', markSvg(colors.white))
write('.wordpress-org/icon.svg', iconSvg())
