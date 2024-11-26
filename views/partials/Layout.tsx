import type { Child, FC } from 'hono/jsx'
import { Header } from './Header'
import site from '../../data/site.json'

export type LayoutProps = {
  title?: string
  description?: string
  content: Child
}

export const Layout: FC<LayoutProps> = ({ title, description, content }) => {
  const about = `${site.description} ${site.copyright}`

  return (
    <html lang="en">
      <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="stylesheet" href="/styles/main.css" />
        <link
          rel="stylesheet"
          href="//fonts.googleapis.com/css?family=Source+Sans+Pro|Source+Serif+Pro:400,600"
        />
        <link rel="shortcut icon" href="/favicon.png" />
        <title>{title}</title>
        <meta name="description" content={description} />
      </head>
      <body>
        <Header about={about} />

        <hr />

        {content}

        {/* TODO: Add back footer at some point? */}
      </body>
    </html>
  )
}
