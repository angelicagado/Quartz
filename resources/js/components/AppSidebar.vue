<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import {
  BarChart3,
  CalendarDays,
  LayoutGrid,
  Ticket,
  Users,
  QrCode,
  Activity,
} from "@lucide/vue";
import { computed } from "vue";
import AppLogo from "@/components/AppLogo.vue";
import NavMain from "@/components/NavMain.vue";
import NavUser from "@/components/NavUser.vue";
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from "@/components/ui/sidebar";
import { dashboard } from "@/routes";
import type { NavItem } from "@/types";

const page = usePage();

const isSuperAdmin = computed(
  () =>
    (page.props.auth.user as { role?: { name?: string } } | null)?.role?.name ===
    "super_admin"
);

const dashboardUrl = computed(() => {
  if (isSuperAdmin.value) {
    return "/super-admin/dashboard";
  }

  return page.props.currentTeam ? dashboard(page.props.currentTeam.slug).url : "/";
});

const mainNavItems = computed<NavItem[]>(() => [
  {
    title: "Dashboard",
    href: dashboardUrl.value,
    icon: LayoutGrid,
  },
  {
    title: "Events",
    href: "/admin/events",
    icon: CalendarDays,
  },
  {
    title: "Users",
    href: "/users",
    icon: Users,
  },
  {
    title: "Reports",
    href: "/reports",
    icon: BarChart3,
  },
  {
    title: "Attendance Scanner",
    href: "/attendance",
    icon: QrCode,
  },
  {
    title: "System Logs",
    href: "/super-admin/logs",
    icon: Activity,
  },

]);
</script>

<template>
  <Sidebar collapsible="icon" variant="inset">
    <SidebarHeader
      class="rounded-lg "
    >
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton
            size="lg"
            class="text-white hover:text-white hover:bg-transparent flex font-serif"
          >
            <Link :href="dashboardUrl">
              <AppLogo />
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
      <!-- <SidebarMenu>
                <SidebarMenuItem>
                    <TeamSwitcher />
                </SidebarMenuItem>
            </SidebarMenu> -->
    </SidebarHeader>

    <SidebarContent>
      <NavMain :items="mainNavItems" />
    </SidebarContent>

    <SidebarFooter>
      <NavUser />
    </SidebarFooter>
  </Sidebar>
  <slot />
</template>
