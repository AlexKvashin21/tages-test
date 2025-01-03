<script setup>

import MainLayout from "@/Layouts/MainLayout.vue";
import {onMounted, reactive, ref} from "vue";
import axios from "axios";
import { createToaster } from "@meforma/vue-toaster";
import {format} from "date-fns";
import { ru } from 'date-fns/locale';


const props = defineProps({
    categories: {
        type: Array,
    },
    tweets: {
        type: Array,
    },
});

const toaster = createToaster({
    position: 'top-right'
});

const isLoadingSendMessage = ref(false)
const isLoadingTweets = ref(false)

const page = ref(1)
const perPage = ref(10)

const form = reactive({
    category_id: 0,
    username: null,
    content: null,
});

const sendMessage = () => {
    if (form.category_id === 0) {
        return toaster.error('Выберете категорию!')
    }

    if (form.category_id && form.username && form.content && !isLoadingSendMessage.value) {
        isLoadingSendMessage.value = true

        axios.post(`/store`, form).then( res => {
            if (res) {
                toaster.success('Сообщение отправлено')
            }

            form.category_id = 0
            form.username = null
            form.content = null
        }).catch(err => {
            console.error(err)

            toaster.error('Произошла ошибка')
        }).finally(() => {
            isLoadingSendMessage.value = false;
        })
    }
}

const showMoreMessages = () => {
    page.value += 1

    isLoadingTweets.value = true

    axios.get(`/more?per_page=${perPage.value}&page=${page.value}`).then(res  => {
        props.tweets.data.push(...res.data.data)
        props.tweets.last_page = res.data.last_page
    }).catch(err => {
        console.error(err)
    }).finally(() => {
        isLoadingTweets.value = false;
    })
}

const formattedDate = (createdAt) => {
    return format(new Date(createdAt), 'd MMMM yyyy HH:mm', {locale: ru});
}

onMounted(() => {
    Echo.channel('tweet')
        .listen('.store.tweet', (res) => {
            props.tweets.data.unshift(res.tweet);
        });
});

</script>

<template>
    <MainLayout>

        <h2 class="mb-5 text-center text-5xl">Отправить сообщение</h2>

        <div class="flex flex-col space-y-1 border-2 border-black p-2">
            <form @submit.prevent="sendMessage">
                <div class="flex flex-col">
                    <label for="category">Категория</label>
                    <select v-model="form.category_id" id="category" name="category_id" class="form-control" required>
                        <option :value="0" disabled selected hidden>Выберите категорию</option>
                        <option v-for="category in categories" :value="category.id">{{ category.title }}</option>
                    </select>
                </div>
                <label class="flex flex-col">
                    Имя
                    <input type="text" name="username" required v-model="form.username" placeholder="Ваше имя">
                </label>
                <label class="flex flex-col">
                    Сообщение
                    <input type="text" name="content" required v-model="form.content" placeholder="Ваш текст">
                </label>

                <button type="submit" class="mt-2 p-1 border-black border-2 btn btn-primary">{{isLoadingSendMessage ? 'Загрузка' : 'Отправить' }}  </button>
            </form>
        </div>

        <h2 class="my-5 text-center text-5xl">Твиты</h2>

        <div v-if="tweets.data.length > 0" class="flex flex-col space-y-2">
            <div v-for="tweet in tweets.data" class="flex flex-col space-y-1 border-2 p-2 border-black">
                <div>
                    ID: {{tweet.id}}
                </div>
                <div>
                    Категория: {{ tweet.category }}
                </div>
                <div>
                    Имя: {{ tweet.username }}
                </div>
                <div>
                    Сообщение: {{ tweet.content }}
                </div>
                <div>
                    Дата: {{ formattedDate(tweet.created_at) }}
                </div>
            </div>
        </div>
        <h3 v-else class="text-center text-3xl">Твитов пока нет!</h3>

        <button
            v-if="tweets.last_page > page"
            @click.prevent="showMoreMessages"
            class="mt-2 p-1 border-black border-2 btn btn-primary block mx-auto"
        >
            {{isLoadingTweets ? 'Загрузка' : 'Больше' }}
        </button>

    </MainLayout>
</template>

