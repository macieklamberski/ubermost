import type { PageView } from 'mikrob'
import { postsWithBooks } from '../../data/posts'
import site from '../../data/site.json'
import { Layout } from '../partials/Layout'
import { Post as PostPartial } from '../partials/Post'
import ErrorPage from './Error'

export const Post: PageView = (props) => {
  const postPath = props.context.req.param('path')
  const post = postsWithBooks.find(({ path }) => path === postPath)

  if (!post) {
    return <ErrorPage {...props} />
  }

  const title = `${post.title} — ${site.title}`
  const description = site.description
  const content = <PostPartial post={post} isList={false} />

  return <Layout title={title} description={description} content={content} />
}

export default Post
