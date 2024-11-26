import type { PageView } from 'mikrob'
import { postsWithBooks } from '../../data/posts'
import site from '../../data/site.json'
import { Layout } from '../partials/Layout'
import { Post } from '../partials/Post'

export const Index: PageView = () => {
  const content = postsWithBooks.map((post) => <Post key={post.path} post={post} isList={true} />)

  return <Layout title={site.title} description={site.description} content={content} />
}

export default Index
